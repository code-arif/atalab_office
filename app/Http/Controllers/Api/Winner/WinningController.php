<?php

namespace App\Http\Controllers\Api\Winner;

use App\Models\DrawWinner;
use App\Models\WeeklyDraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class WinningController extends Controller
{
    /**
     * Get user's winnings
     */
    public function myWinnings()
    {
        $winnings = DrawWinner::with(['weeklyDraw', 'donation'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalWon = DrawWinner::where('user_id', auth()->id())->sum('amount_won');
        $totalClaimed = DrawWinner::where('user_id', auth()->id())
            ->where('claimed', true)
            ->sum('amount_won');

        return response()->json([
            'success' => true,
            'data' => [
                'winnings' => $winnings,
                'summary' => [
                    'total_won' => $totalWon,
                    'total_claimed' => $totalClaimed,
                    'pending_claim' => $totalWon - $totalClaimed,
                ]
            ]
        ]);
    }

    /**
     * Claim a winning
     */
    public function claim($id)
    {
        $winner = DrawWinner::with('weeklyDraw')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        if (!$winner->canClaim()) {
            return response()->json([
                'success' => false,
                'message' => 'This winning cannot be claimed. Either it is already claimed or the claim period has expired.'
            ], 400);
        }

        try {
            DB::transaction(function () use ($winner) {
                $winner->update([
                    'claimed' => true,
                    'claimed_at' => now(),
                    'payout_status' => 'processing',
                ]);

                // TODO: Integrate with Stripe Transfer/Payout API
                // $this->processPayoutToUser($winner);
            });

            return response()->json([
                'success' => true,
                'message' => 'Claim successful! Your payout is being processed.',
                'data' => $winner->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process claim: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user is a winner in current draw
     */
    public function checkWinningStatus()
    {
        $latestDraw = WeeklyDraw::where('status', 'claiming')
            ->orWhere('status', 'completed')
            ->orderBy('week_number', 'desc')
            ->first();

        if (!$latestDraw) {
            return response()->json([
                'success' => true,
                'is_winner' => false,
                'message' => 'No completed draws yet'
            ]);
        }

        $winning = DrawWinner::where('user_id', auth()->id())
            ->where('weekly_draw_id', $latestDraw->id)
            ->first();

        return response()->json([
            'success' => true,
            'is_winner' => $winning !== null,
            'data' => $winning,
            'draw' => $latestDraw
        ]);
    }
}
