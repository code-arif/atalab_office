<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Donation;
use App\Models\DrawWinner;
use App\Models\WeeklyDraw;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WeeklyDrawService
{
    /**
     * BUSINESS RULES:
     * - Odds: 1 in 400 (every 400 participants = 1 winner)
     * - Admin Fee: 7.5%
     * - Minimum: 100 participants to run a draw
     * - Always round UP winners (350 participants = 1 winner, not 0.875)
     */
    private const ADMIN_FEE_PERCENTAGE = 0.075; // 7.5%
    private const ODDS_RATIO = 400; // 1 winner per 400 participants
    private const MINIMUM_PARTICIPANTS = 100; // Minimum to run draw

    /**
     * Get current active draw
     */
    public function getCurrentDraw(): WeeklyDraw
    {
        $draw = WeeklyDraw::where('status', 'active')
            ->orWhere('status', 'claiming')
            ->orderBy('week_number', 'desc')
            ->first();

        if (!$draw) {
            throw new Exception('No active draw available');
        }

        return $draw;
    }

    /**
     * Create new weekly draw (AUTOMATED - Monday 12:00 AM)
     */
    public function createNewDraw(): WeeklyDraw
    {
        return DB::transaction(function () {
            $activeDraw = WeeklyDraw::where('status', 'active')->first();
            if ($activeDraw) {
                throw new Exception('Active draw already exists');
            }

            $lastDraw = WeeklyDraw::orderBy('week_number', 'desc')->first();
            $weekNumber = $lastDraw ? $lastDraw->week_number + 1 : 1;

            $startDate = Carbon::now('Asia/Dhaka')->startOfWeek(Carbon::MONDAY)->setTime(0, 0, 0);
            $endDate = $startDate->copy()->endOfWeek(Carbon::SUNDAY)->setTime(17, 0, 0);
            $countdownEndsAt = $endDate->copy();
            $claimDeadline = $endDate->copy()->addDay()->setTime(5, 0, 0);

            $draw = WeeklyDraw::create([
                'week_number' => $weekNumber,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'countdown_ends_at' => $countdownEndsAt,
                'claim_deadline' => $claimDeadline,
                'status' => 'active',
                'total_pool' => 0,
                'total_participants' => 0,
                'total_recipients' => 0,
                'admin_commission' => 0,
                'winners_selected' => false,
            ]);

            Log::info('New weekly draw created', [
                'week_number' => $weekNumber,
                'start' => $startDate->toDateTimeString(),
                'end' => $endDate->toDateTimeString(),
            ]);

            return $draw;
        });
    }

    /**
     * Finalize draw (AUTOMATED - Sunday 5 PM)
     */
    public function finalizeDraw(int $weekId): WeeklyDraw
    {
        return DB::transaction(function () use ($weekId) {
            $draw = WeeklyDraw::findOrFail($weekId);

            if ($draw->status !== 'active') {
                throw new Exception('Draw is not active');
            }

            $draw->update(['status' => 'claiming']);

            Log::info('Draw finalized', [
                'week_id' => $weekId,
                'week_number' => $draw->week_number
            ]);

            return $draw->fresh();
        });
    }

    /**
     * Select winners with DYNAMIC 1:400 RATIO CALCULATION
     */
    public function selectWinners(int $weekId): array
    {
        return DB::transaction(function () use ($weekId) {
            $draw = WeeklyDraw::findOrFail($weekId);

            if ($draw->winners_selected) {
                throw new Exception('Winners already selected for this draw');
            }

            // Get total pool and participants
            $totalPool = Donation::where('week_id', $weekId)
                ->where('stripe_payment_status', 'completed')
                ->sum('amount');

            $totalParticipants = Donation::where('week_id', $weekId)
                ->where('stripe_payment_status', 'completed')
                ->distinct('user_id')
                ->count('user_id');

            // DYNAMIC MINIMUM CHECK
            if ($totalParticipants < self::MINIMUM_PARTICIPANTS) {
                Log::warning('Insufficient participants for draw', [
                    'week_id' => $weekId,
                    'participants' => $totalParticipants,
                    'minimum_required' => self::MINIMUM_PARTICIPANTS,
                ]);

                throw new Exception(
                    "Insufficient participants for draw. Minimum {self::MINIMUM_PARTICIPANTS} required, found {$totalParticipants}"
                );
            }

            // CALCULATE WINNERS DYNAMICALLY (1:400 ratio, ALWAYS ROUND UP)
            // Examples:
            // 350 participants ÷ 400 = 0.875 → ceil() = 1 winner
            // 600 participants ÷ 400 = 1.5 → ceil() = 2 winners
            // 1300 participants ÷ 400 = 3.25 → ceil() = 4 winners
            // 10500 participants ÷ 400 = 26.25 → ceil() = 27 winners
            $numberOfWinners = (int) ceil($totalParticipants / self::ODDS_RATIO);

            // CALCULATE ADMIN COMMISSION (7.5% of total pool)
            $adminCommission = $totalPool * self::ADMIN_FEE_PERCENTAGE;
            $distributionPool = $totalPool - $adminCommission;

            // CALCULATE AMOUNT PER WINNER (Equal distribution)
            $amountPerWinner = $distributionPool / $numberOfWinners;

            // Log detailed calculation
            Log::info('Dynamic Draw Calculation', [
                'week_id' => $weekId,
                'participants' => $totalParticipants,
                'odds_ratio' => self::ODDS_RATIO . ':1',
                'calculated_winners' => $totalParticipants / self::ODDS_RATIO,
                'final_winners' => $numberOfWinners . ' (rounded up)',
                'total_pool' => '$' . number_format($totalPool, 2),
                'admin_commission_rate' => (self::ADMIN_FEE_PERCENTAGE * 100) . '%',
                'admin_commission' => '$' . number_format($adminCommission, 2),
                'distribution_pool' => '$' . number_format($distributionPool, 2),
                'per_winner' => '$' . number_format($amountPerWinner, 2),
            ]);

            // VERIFY: Distribution pool should be positive
            if ($distributionPool <= 0) {
                throw new Exception('Invalid distribution pool calculated');
            }

            // VERIFY: Amount per winner should be reasonable
            if ($amountPerWinner < 1) {
                throw new Exception('Amount per winner too low. Pool insufficient.');
            }

            // Get eligible donations with 6-month exclusion
            $sixMonthsAgo = Carbon::now()->subMonths(6);
            $recentWinnerUserIds = DrawWinner::where('created_at', '>=', $sixMonthsAgo)
                ->pluck('user_id')
                ->toArray();

            Log::info('6-Month Exclusion Check', [
                'excluded_users_count' => count($recentWinnerUserIds),
                'lookback_date' => $sixMonthsAgo->toDateString(),
            ]);

            // Get eligible donations
            $eligibleDonations = Donation::where('week_id', $weekId)
                ->where('stripe_payment_status', 'completed')
                ->where('is_eligible_for_draw', true)
                ->whereNotIn('user_id', $recentWinnerUserIds)
                ->inRandomOrder()
                ->limit($numberOfWinners)
                ->get();

            // CHECK: Enough eligible participants
            if ($eligibleDonations->count() < $numberOfWinners) {
                Log::warning('Not enough eligible participants after exclusion', [
                    'needed' => $numberOfWinners,
                    'available' => $eligibleDonations->count(),
                    'excluded' => count($recentWinnerUserIds),
                ]);

                throw new Exception(
                    "Not enough eligible participants. Need {$numberOfWinners}, found {$eligibleDonations->count()} (after 6-month exclusion)"
                );
            }

            // Create winners
            $winners = [];
            foreach ($eligibleDonations as $donation) {
                $winner = DrawWinner::create([
                    'weekly_draw_id' => $draw->id,
                    'user_id' => $donation->user_id,
                    'donation_id' => $donation->id,
                    'amount_won' => round($amountPerWinner, 2), // Round to 2 decimals
                    'claimed' => false,
                    'payout_status' => 'pending',
                ]);
                $winners[] = $winner;
            }

            // Update draw
            $draw->update([
                'total_pool' => $totalPool,
                'total_participants' => $totalParticipants,
                'total_recipients' => $numberOfWinners,
                'admin_commission' => round($adminCommission, 2),
                'winners_selected' => true,
                'status' => 'completed',
            ]);

            Log::info('Winners selected successfully', [
                'week_id' => $weekId,
                'week_number' => $draw->week_number,
                'winners_count' => count($winners),
                'total_distributed' => '$' . number_format($distributionPool, 2),
                'per_winner' => '$' . number_format($amountPerWinner, 2),
            ]);

            return [
                'winners' => $winners,
                'total_distributed' => round($distributionPool, 2),
                'admin_commission' => round($adminCommission, 2),
                'recipients' => $numberOfWinners,
                'per_winner' => round($amountPerWinner, 2),
            ];
        });
    }

    /**
     * Get draw statistics
     */
    public function getDrawStats(int $weekId): array
    {
        $draw = WeeklyDraw::findOrFail($weekId);

        // Calculate expected winners based on current participants
        $expectedWinners = $draw->total_participants > 0
            ? (int) ceil($draw->total_participants / self::ODDS_RATIO)
            : 0;

        return [
            'week_number' => $draw->week_number,
            'status' => $draw->status,
            'total_pool' => $draw->total_pool,
            'total_participants' => $draw->total_participants,
            'expected_winners' => $expectedWinners, // Dynamic calculation
            'actual_recipients' => $draw->total_recipients,
            'admin_commission' => $draw->admin_commission,
            'countdown_ends_at' => $draw->countdown_ends_at,
            'claim_deadline' => $draw->claim_deadline,
            'odds' => '1:' . self::ODDS_RATIO,
            'minimum_participants' => self::MINIMUM_PARTICIPANTS,
        ];
    }

    /**
     * Get all draws (Admin)
     */
    public function getAllDraws(int $page = 1, int $perPage = 20)
    {
        return WeeklyDraw::orderBy('week_number', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get overview statistics (Admin)
     */
    public function getOverviewStats(): array
    {
        return [
            'total_donations' => Donation::where('stripe_payment_status', 'completed')->sum('amount'),
            'total_participants' => Donation::where('stripe_payment_status', 'completed')
                ->distinct('user_id')->count(),
            'total_winners' => DrawWinner::count(),
            'total_distributed' => DrawWinner::sum('amount_won'),
            'total_commission' => WeeklyDraw::sum('admin_commission'),
            'active_draws' => WeeklyDraw::where('status', 'active')->count(),
            'current_odds' => '1:' . self::ODDS_RATIO,
            'admin_fee_rate' => (self::ADMIN_FEE_PERCENTAGE * 100) . '%',
        ];
    }
}
