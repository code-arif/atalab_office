<?php

namespace App\Http\Controllers\Api\Draw;

use App\Models\WeeklyDraw;
use Illuminate\Http\Request;
use App\Services\DrawService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DrawController extends Controller
{
    public function __construct(private DrawService $drawService) {}

    /**
     * Get current active draw
     */
    public function current()
    {
        $draw = $this->drawService->getCurrentActiveDraw();

        if (!$draw) {
            return response()->json([
                'success' => false,
                'message' => 'No active draw at this time'
            ], 404);
        }

        $statistics = $this->drawService->getDrawStatistics($draw);

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }

    /**
     * Get draw history
     */
    public function history(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $draws = WeeklyDraw::with(['winners', 'donations'])
            ->where('status', 'completed')
            ->orderBy('week_number', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $draws
        ]);
    }

    /**
     * Get specific draw details
     */
    public function show($id)
    {
        $draw = WeeklyDraw::with(['winners.user', 'donations'])
            ->findOrFail($id);

        $statistics = $this->drawService->getDrawStatistics($draw);

        return response()->json([
            'success' => true,
            'data' => [
                'draw' => $draw,
                'statistics' => $statistics,
            ]
        ]);
    }

    /**
     * Get draw leaderboard (top donors)
     */
    public function leaderboard($drawId)
    {
        $draw = WeeklyDraw::findOrFail($drawId);

        $topDonors = $draw->donations()
            ->select('user_id', DB::raw('SUM(amount) as total_donated'), DB::raw('COUNT(*) as donation_count'))
            ->with('user:id,name,email')
            ->groupBy('user_id')
            ->orderBy('total_donated', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $topDonors
        ]);
    }
}
