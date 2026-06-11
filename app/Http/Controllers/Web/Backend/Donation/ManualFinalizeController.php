<?php

namespace App\Http\Controllers\Web\Backend\Donation;

use App\Http\Controllers\Controller;
use App\Models\DrawAutomateSetting;
use App\Models\Donation;
use App\Models\WeeklyDraw;
use App\Services\WeeklyDrawService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManualFinalizeController extends Controller
{
    protected WeeklyDrawService $weeklyDrawService;

    public function __construct(WeeklyDrawService $weeklyDrawService)
    {
        $this->weeklyDrawService = $weeklyDrawService;
    }

    /**
     * Show the manual finalization page for a specific draw.
     * Displays all donation information and provides the manual finalize button.
     */
    public function show(int $id)
    {
        $draw = WeeklyDraw::findOrFail($id);

        if ($draw->status !== 'active') {
            return redirect()->route('weekly-draws.index')
                ->with('error', 'Only active draws can be finalized manually.');
        }

        $settings = DrawAutomateSetting::firstOrCreate([], [
            'admin_fee_percentage' => 10,
            'odds_ratio' => 250,
            'minimum_participants' => 100,
            'winner_exclusion_months' => 12,
        ]);

        // Get donations for the specific week (always show week-level data)
        $donations = Donation::where('week_id', $draw->id)
            ->where('stripe_payment_status', 'completed')
            ->with(['user', 'weeklyDraw'])
            ->orderBy('amount', 'desc')
            ->get();

        // Calculate totals — use cycle-level data if draw_cycle_id exists, otherwise week-level
        $cycleId = $draw->draw_cycle_id;

        if ($cycleId) {
            $totalPool = (float) Donation::whereHas('weeklyDraw', function ($q) use ($cycleId) {
                $q->where('draw_cycle_id', $cycleId);
            })->where('stripe_payment_status', 'completed')->sum('amount');

            $totalParticipants = Donation::whereHas('weeklyDraw', function ($q) use ($cycleId) {
                $q->where('draw_cycle_id', $cycleId);
            })->where('stripe_payment_status', 'completed')
                ->distinct('user_id')->count('user_id');

            // Cycle-level donations for context
            $cycleDonations = Donation::whereHas('weeklyDraw', function ($q) use ($cycleId) {
                $q->where('draw_cycle_id', $cycleId);
            })->where('stripe_payment_status', 'completed')
                ->with(['user', 'weeklyDraw'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Fallback: no cycle assigned, use this week's donations only
            $totalPool = (float) $donations->sum('amount');
            $totalParticipants = $donations->unique('user_id')->count();
            $cycleDonations = $donations;
        }

        $adminCommission = $totalPool * ($settings->admin_fee_percentage / 100);
        $distributionPool = $totalPool - $adminCommission;
        $expectedWinners = $totalParticipants >= 250 ? (int) ceil($totalParticipants / $settings->odds_ratio) : 0;
        // $expectedWinners = 0;

        return view('backend.layouts.donation_&_draw.draw_finalize', compact(
            'draw',
            'settings',
            'totalPool',
            'totalParticipants',
            'adminCommission',
            'distributionPool',
            'expectedWinners',
            'donations',
            'cycleDonations'
        ));
    }

    /**
     * Execute the manual finalization.
     * Follows the same logic as AutomateWeeklyDraws::finalizeAndSelectWinners().
     */
    public function finalize(int $id): JsonResponse
    {
        try {
            $draw = WeeklyDraw::findOrFail($id);

            if ($draw->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'This draw is no longer active and cannot be finalized.',
                ], 400);
            }

            if ($draw->winners_selected) {
                return response()->json([
                    'success' => false,
                    'message' => 'Winners have already been selected for this draw.',
                ], 400);
            }

            // Step 1: Finalize the draw (change status to 'claiming')
            $draw = $this->weeklyDrawService->finalizeDraw($draw->id);

            // Step 2: Select winners using the same service logic
            $result = $this->weeklyDrawService->selectWinners($draw->id);

            if (isset($result['rollover']) && $result['rollover']) {
                return response()->json([
                    'success' => true,
                    'rollover' => true,
                    'message' => "Draw rolled over. Only {$result['participants']} participants found, which is below the minimum required.",
                ]);
            }

            return response()->json([
                'success' => true,
                'rollover' => false,
                'message' => 'Draw finalized and winners selected successfully!',
                'data' => [
                    'recipients' => $result['recipients'],
                    'total_distributed' => number_format($result['total_distributed'], 2),
                    'admin_commission' => number_format($result['admin_commission'], 2),
                    'per_winner' => number_format($result['per_winner'], 2),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Finalization failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
