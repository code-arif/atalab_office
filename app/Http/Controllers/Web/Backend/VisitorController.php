<?php

namespace App\Http\Controllers\Web\Backend;

use Carbon\Carbon;
use App\Models\Visitor;
use Illuminate\Http\Request;
use App\Models\VisitorStatistic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class VisitorController extends Controller
{
    /**
     * Track visitor from React frontend
     */
    public function trackVisitor(Request $request)
    {
        try {
            $ip = $request->ip();
            $today = today(config('app.timezone'));

            // Redis cache use kore same IP daily te ekbar count
            $cacheKey = "visitor_tracked_{$ip}_{$today->format('Y-m-d')}";

            if (Cache::has($cacheKey)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Already tracked today',
                    'cached' => true
                ], 200);
            }

            // Get location data (optional - using request headers)
            $userAgent = $request->header('User-Agent');
            $country = $request->header('CF-IPCountry', 'Unknown');

            // Insert or update visitor record
            DB::transaction(function () use ($ip, $today, $userAgent, $country) {
                Visitor::updateOrCreate(
                    [
                        'ip_address' => $ip,
                        'visit_date' => $today
                    ],
                    [
                        'user_agent' => $userAgent,
                        'country' => $country,
                        'visit_count' => DB::raw('visit_count + 1')
                    ]
                );

                // Update daily statistics
                $this->updateDailyStatistics($today);
            });

            // Cache for 24 hours
            Cache::put($cacheKey, true, now()->endOfDay());

            return response()->json([
                'success' => true,
                'message' => 'Visitor tracked successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Visitor tracking failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Tracking failed'
            ], 500);
        }
    }

    /**
     * Update daily statistics (Redis optimized)
     */
    private function updateDailyStatistics($date)
    {
        // Cache statistics for 5 minutes to reduce DB load
        $stats = Cache::remember("visitor_stats_{$date->format('Y-m-d')}", 300, function () use ($date) {
            return [
                'unique_visitors' => Visitor::whereDate('visit_date', $date)->count(),
                'total_visitors' => Visitor::whereDate('visit_date', $date)->sum('visit_count')
            ];
        });

        VisitorStatistic::updateOrCreate(
            ['date' => $date],
            [
                'unique_visitors' => $stats['unique_visitors'],
                'total_visitors' => $stats['total_visitors']
            ]
        );
    }

    /**
     * Get visitor statistics for dashboard
     */
    public function getVisitorStats(Request $request)
    {
        try {
            $today = today(config('app.timezone'));

            // Cache for 5 minutes
            $stats = Cache::remember('dashboard_visitor_stats', 300, function () use ($today) {
                // Today's stats
                $todayStats = VisitorStatistic::whereDate('date', $today)->first();

                // Last 7 days trend
                $weekTrend = VisitorStatistic::where('date', '>=', $today->copy()->subDays(6))
                    ->orderBy('date', 'asc')
                    ->get()
                    ->map(fn($stat) => [
                        'date' => $stat->date->format('M d'),
                        'visitors' => $stat->unique_visitors
                    ]);

                // Total statistics
                $totalVisitors = Visitor::distinct('ip_address')->count('ip_address');
                $totalViews = Visitor::sum('visit_count');

                // Current month stats
                $monthStart = $today->copy()->startOfMonth();
                $monthStats = VisitorStatistic::where('date', '>=', $monthStart)
                    ->sum('unique_visitors');

                return [
                    'today' => [
                        'unique' => $todayStats->unique_visitors ?? 0,
                        'total' => $todayStats->total_visitors ?? 0
                    ],
                    'this_month' => $monthStats,
                    'all_time' => [
                        'unique' => $totalVisitors,
                        'total' => $totalViews
                    ],
                    'week_trend' => $weekTrend,
                    'live_count' => Visitor::whereDate('visit_date', $today)
                        ->whereTime('updated_at', '>=', now()->subMinutes(5))
                        ->count() // Last 5 minutes
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch stats'
            ], 500);
        }
    }
}
