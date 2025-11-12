<?php

namespace App\Services;

use App\Models\DrawWinner;
use App\Models\WeeklyDraw;
use Illuminate\Support\Facades\DB;

class DrawWinnerService
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Get winners by week number
     */
    public function getWinnersByWeek(int $weekNumber): array
    {
        $draw = WeeklyDraw::where('week_number', $weekNumber)->firstOrFail();

        $winners = DrawWinner::with(['user', 'donation'])
            ->where('weekly_draw_id', $draw->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'draw' => $draw,
            'winners' => $winners,
        ];
    }

    /**
     * Get latest winners
     */
    public function getLatestWinners(): array
    {
        $latestDraw = WeeklyDraw::where('winners_selected', true)
            ->orderBy('week_number', 'desc')
            ->first();

        if (!$latestDraw) {
            throw new \Exception('No completed draws found');
        }

        $winners = DrawWinner::with(['user', 'donation'])
            ->where('weekly_draw_id', $latestDraw->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'draw' => $latestDraw,
            'winners' => $winners,
        ];
    }

    /**
     * Get all winners (Admin)
     */
    public function getAllWinners(int $page = 1, int $perPage = 50)
    {
        return DrawWinner::with(['user', 'weeklyDraw', 'donation'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Process payout for a winner (Admin)
     */
    public function processPayout(int $winnerId): DrawWinner
    {
        return DB::transaction(function () use ($winnerId) {
            $winner = DrawWinner::with(['user', 'weeklyDraw'])->findOrFail($winnerId);

            if ($winner->payout_status === 'completed') {
                throw new \Exception('Payout already processed for this winner');
            }

            if (!$winner->claimed) {
                throw new \Exception('Winner has not claimed their prize yet');
            }

            try {
                // Process payout via Stripe
                $payoutResult = $this->stripeService->processPayoutToWinner(
                    $winner->user->email,
                    $winner->amount_won,
                    "Winner payout for Week #{$winner->weeklyDraw->week_number}"
                );

                $winner->update([
                    'payout_stripe_id' => $payoutResult['payout_id'],
                    'payout_status' => 'processing',
                ]);

                // In production, you would have a webhook to update this to 'completed'
                // For now, we'll mark it as processing

                return $winner->fresh();
            } catch (\Exception $e) {
                $winner->update([
                    'payout_status' => 'failed',
                ]);

                throw new \Exception('Failed to process payout: ' . $e->getMessage());
            }
        });
    }

    /**
     * Get pending payouts (Admin)
     */
    public function getPendingPayouts(): array
    {
        $pendingWinners = DrawWinner::with(['user', 'weeklyDraw'])
            ->where('claimed', true)
            ->where('payout_status', 'pending')
            ->orderBy('claimed_at', 'asc')
            ->get();

        $processingWinners = DrawWinner::with(['user', 'weeklyDraw'])
            ->where('payout_status', 'processing')
            ->orderBy('updated_at', 'desc')
            ->get();

        return [
            'pending' => $pendingWinners,
            'processing' => $processingWinners,
            'total_pending_amount' => $pendingWinners->sum('amount_won'),
            'total_processing_amount' => $processingWinners->sum('amount_won'),
        ];
    }

    /**
     * Mark winner as claimed
     */
    public function claimPrize(int $winnerId): DrawWinner
    {
        return DB::transaction(function () use ($winnerId) {
            $winner = DrawWinner::findOrFail($winnerId);

            if ($winner->claimed) {
                throw new \Exception('Prize already claimed');
            }

            $winner->update([
                'claimed' => true,
                'claimed_at' => now(),
            ]);

            return $winner->fresh();
        });
    }

    /**
     * Get winner statistics
     */
    public function getWinnerStatistics(): array
    {
        $totalWinners = DrawWinner::count();
        $totalAmountWon = DrawWinner::sum('amount_won');
        $claimedWinners = DrawWinner::where('claimed', true)->count();
        $unclaimedWinners = DrawWinner::where('claimed', false)->count();

        $paidOutAmount = DrawWinner::where('payout_status', 'completed')->sum('amount_won');
        $pendingPayoutAmount = DrawWinner::whereIn('payout_status', ['pending', 'processing'])
            ->sum('amount_won');

        return [
            'total_winners' => $totalWinners,
            'total_amount_won' => $totalAmountWon,
            'claimed_winners' => $claimedWinners,
            'unclaimed_winners' => $unclaimedWinners,
            'paid_out_amount' => $paidOutAmount,
            'pending_payout_amount' => $pendingPayoutAmount,
        ];
    }
}
