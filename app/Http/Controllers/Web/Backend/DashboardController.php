<?php

namespace App\Http\Controllers\Web\Backend;


use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\DrawWinner;
use App\Models\User;
use App\Models\Visitor;
use App\Models\WeeklyDraw;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard view with comprehensive statistics
     */
    public function index()
    {
        // Current Active Draw
        $activeDraw = WeeklyDraw::where('status', 'active')
            ->first();

        // Total Statistics
        $totalDonors = User::where('role', 'donor')->count();
        $totalDonations = Donation::where('stripe_payment_status', 'completed')->sum('amount');
        $totalWinners = DrawWinner::where('claimed', true)->count();
        $pendingPayouts = DrawWinner::where('payout_status', 'pending')->count();


        // Active Draw Statistics
        $activeDrawStats = null;
        if ($activeDraw) {
            // Ensure countdown_ends_at is not null and is a valid date
            $endsAt = $activeDraw->countdown_ends_at
                ? Carbon::parse($activeDraw->countdown_ends_at)
                : Carbon::now()->addHours(24); // fallback if null (optional)

            $now = Carbon::now();

            // Safe way: use timestamp only if valid
            $timeRemaining = $endsAt->isFuture()
                ? $now->diffInSeconds($endsAt, false)
                : 0;

            $hasEnded = !$endsAt->isFuture();

            $activeDrawStats = [
                'week_number' => $activeDraw->week_number,
                'total_pool' => $activeDraw->total_pool,
                'total_participants' => $activeDraw->total_participants,
                'ends_at_timestamp' => $endsAt->timestamp,
                'time_remaining' => $timeRemaining,
                'has_ended' => $hasEnded,
            ];
        }

        // Last 7 Days Donation Trend (for chart)
        $donationTrend = Donation::where('stripe_payment_status', 'completed')
            ->where('created_at', '>=', now()->subDays(7))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as total_count')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Recent Donations (Last 1 hour for scrollable feed)
        $recentDonations = Donation::with('user:id,name,email')
            ->where('stripe_payment_status', 'completed')
            ->where('created_at', '>=', now()->subHour())
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($donation) {
                return [
                    'id' => $donation->id,
                    'user_name' => $donation->user ? $donation->user->name : 'Anonymous',
                    'amount' => $donation->amount,
                    'created_at' => $donation->created_at->diffForHumans(),
                    'timestamp' => $donation->created_at->timestamp,
                ];
            });

        // return $recentDonations;exit();

        // Weekly Performance (Last 4 weeks)
        $weeklyPerformance = WeeklyDraw::where('status', '!=', 'active')
            ->orderBy('week_number', 'desc')
            ->limit(4)
            ->get()
            ->map(function ($draw) {
                return [
                    'week' => 'Week ' . $draw->week_number,
                    'pool' => $draw->total_pool,
                    'participants' => $draw->total_participants,
                    'winners' => $draw->total_recipients,
                    'commission' => $draw->admin_commission,
                ];
            });

        // Top Donors (Current Active Draw)
        $topDonors = [];
        if ($activeDraw) {
            $topDonors = Donation::where('week_id', $activeDraw->id)
                ->where('stripe_payment_status', 'completed')
                ->select('user_id', DB::raw('SUM(amount) as total_donated'))
                ->with('user:id,name,email')
                ->groupBy('user_id')
                ->orderBy('total_donated', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($donation) {
                    return [
                        'name' => $donation->user ? $donation->user->name : 'Anonymous',
                        'email' => $donation->user ? $donation->user->email : 'N/A',
                        'total_donated' => $donation->total_donated,
                    ];
                });
        }

        // Payment Status Distribution
        $paymentStatusStats = Donation::select('stripe_payment_status', DB::raw('count(*) as count'))
            ->groupBy('stripe_payment_status')
            ->get()
            ->pluck('count', 'stripe_payment_status');

        // Visitor Statistics (NEW)
        $visitorStats = Cache::remember('admin_dashboard_visitor_stats', 300, function () {
            $today = today(config('app.timezone'));
            $todayStats = \App\Models\VisitorStatistic::whereDate('date', $today)->first();

            return [
                'today_unique' => $todayStats->unique_visitors ?? 0,
                'today_total' => $todayStats->total_visitors ?? 0,
                'all_time_unique' => Visitor::distinct('ip_address')->count('ip_address'),
                'live_now' => Visitor::whereDate('visit_date', $today)
                    ->whereTime('updated_at', '>=', now()->subMinutes(5))
                    ->count()
            ];
        });

        return view('backend.layouts.dashboard', compact(
            'totalDonors',
            'totalDonations',
            'totalWinners',
            'pendingPayouts',
            'activeDraw',
            'activeDrawStats',
            'donationTrend',
            'recentDonations',
            'weeklyPerformance',
            'topDonors',
            'paymentStatusStats',
            'visitorStats' // NEW
        ));
    }

    /**
     * API endpoint for real-time donation updates
     */
    public function recentDonationsApi()
    {
        $donations = Donation::with('user:id,name,email')
            ->where('stripe_payment_status', 'completed')
            ->where('created_at', '>=', now()->subHour())
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($donation) {
                return [
                    'id' => $donation->id,
                    'user_name' => $donation->user ? $donation->user->name : 'Anonymous',
                    'amount' => $donation->amount,
                    'created_at' => $donation->created_at->diffForHumans(),
                    'timestamp' => $donation->created_at->timestamp,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $donations
        ]);
    }

    /**
     * API endpoint for real-time statistics
     */
    public function liveStatsApi()
    {
        $activeDraw = WeeklyDraw::where('status', 'active')->first();

        $stats = [
            'total_pool' => $activeDraw ? $activeDraw->total_pool : 0,
            'total_participants' => $activeDraw ? $activeDraw->total_participants : 0,
            'time_remaining' => $activeDraw && $activeDraw->countdown_ends_at ?
                Carbon::parse($activeDraw->countdown_ends_at)->diffInSeconds(now()) : 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
