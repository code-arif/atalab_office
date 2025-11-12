<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Donation;
use App\Models\DrawWinner;
use App\Models\WeeklyDraw;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WeeklyDrawService
{
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
            throw new \Exception('No active draw available');
        }

        return $draw;
    }

    /**
     * Get draw by week number
     */
    public function getDrawByWeekNumber(int $weekNumber): WeeklyDraw
    {
        $draw = WeeklyDraw::where('week_number', $weekNumber)->first();

        if (!$draw) {
            throw new \Exception('Draw not found');
        }

        return $draw;
    }

    /**
     * Get draw statistics
     */
    public function getDrawStats(int $weekId): array
    {
        $draw = WeeklyDraw::findOrFail($weekId);

        $stats = [
            'week_number' => $draw->week_number,
            'status' => $draw->status,
            'total_pool' => $draw->total_pool,
            'total_participants' => $draw->total_participants,
            'total_recipients' => $draw->total_recipients,
            'admin_commission' => $draw->admin_commission,
            'countdown_ends_at' => $draw->countdown_ends_at,
            'claim_deadline' => $draw->claim_deadline,
            'time_remaining' => $this->getTimeRemaining($draw),
        ];

        // Get donation breakdown
        $donationStats = Donation::where('week_id', $weekId)
            ->where('stripe_payment_status', 'completed')
            ->selectRaw('
                payment_type,
                COUNT(*) as count,
                SUM(amount) as total
            ')
            ->groupBy('payment_type')
            ->get();

        $stats['donation_breakdown'] = $donationStats;

        return $stats;
    }

    /**
     * Create new weekly draw (AUTOMATED)
     */
    public function createNewDraw(): WeeklyDraw
    {
        return DB::transaction(function () {
            // Check if active draw exists
            $activeDraw = WeeklyDraw::where('status', 'active')->first();
            if ($activeDraw) {
                throw new \Exception('Active draw already exists');
            }

            // Get last week number
            $lastDraw = WeeklyDraw::orderBy('week_number', 'desc')->first();
            $weekNumber = $lastDraw ? $lastDraw->week_number + 1 : 1;

            // Calculate dates precisely
            $startDate = Carbon::now()->startOfWeek(Carbon::MONDAY)->setTime(0, 0, 0);
            $endDate = $startDate->copy()->addDays(6)->setTime(17, 0, 0); // Sunday 5 PM
            $countdownEndsAt = $endDate->copy();
            $claimDeadline = $countdownEndsAt->copy()->addHours(12); // Sunday 5 AM next day

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
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            return $draw;
        });
    }

    /**
     * Finalize draw and start claiming period (AUTOMATED)
     */
    public function finalizeDraw(int $weekId): WeeklyDraw
    {
        return DB::transaction(function () use ($weekId) {
            $draw = WeeklyDraw::findOrFail($weekId);

            if ($draw->status !== 'active') {
                throw new \Exception('Draw is not active');
            }

            // Pause donations by changing status
            $draw->update([
                'status' => 'claiming',
            ]);

            Log::info('Draw finalized', [
                'week_id' => $weekId,
                'week_number' => $draw->week_number
            ]);

            return $draw->fresh();
        });
    }

    /**
     * Select winners with 6-MONTH EXCLUSION RULE (AUTOMATED)
     */
    public function selectWinners(int $weekId): array
    {
        return DB::transaction(function () use ($weekId) {
            $draw = WeeklyDraw::findOrFail($weekId);

            if ($draw->winners_selected) {
                throw new \Exception('Winners already selected for this draw');
            }

            if ($draw->total_pool < 100) {
                throw new \Exception('Insufficient pool amount for draw (minimum $100 required)');
            }

            // Calculate distribution
            $distribution = $this->calculateDistribution($draw->total_participants, $draw->total_pool);

            // ** CRITICAL: Get eligible donations with 6-month exclusion **
            $sixMonthsAgo = Carbon::now()->subMonths(6);

            // Get user IDs who won in last 6 months
            $recentWinnerUserIds = DrawWinner::where('created_at', '>=', $sixMonthsAgo)
                ->pluck('user_id')
                ->toArray();

            Log::info('6-month exclusion check', [
                'recent_winners_count' => count($recentWinnerUserIds),
                'excluded_user_ids' => $recentWinnerUserIds
            ]);

            // Get eligible donations (exclude recent winners)
            $eligibleDonations = Donation::where('week_id', $weekId)
                ->where('stripe_payment_status', 'completed')
                ->where('is_eligible_for_draw', true)
                ->whereNotIn('user_id', $recentWinnerUserIds) // ** EXCLUDE RECENT WINNERS **
                ->inRandomOrder()
                ->limit($distribution['recipients'])
                ->get();

            if ($eligibleDonations->count() < $distribution['recipients']) {
                throw new \Exception(
                    "Not enough eligible participants. Need {$distribution['recipients']}, found {$eligibleDonations->count()}"
                );
            }

            // Calculate amount per recipient
            $amountPerRecipient = $distribution['distribution_pool'] / $distribution['recipients'];

            $winners = [];
            foreach ($eligibleDonations as $donation) {
                $winner = DrawWinner::create([
                    'weekly_draw_id' => $draw->id,
                    'user_id' => $donation->user_id,
                    'donation_id' => $donation->id,
                    'amount_won' => $amountPerRecipient,
                    'claimed' => false,
                    'payout_status' => 'pending',
                ]);

                $winners[] = $winner;
            }

            // Update draw to completed
            $draw->update([
                'winners_selected' => true,
                'total_recipients' => $distribution['recipients'],
                'admin_commission' => $distribution['admin_commission'],
                'status' => 'completed', // Mark as completed after winner selection
            ]);

            Log::info('Winners selected successfully', [
                'week_id' => $weekId,
                'winners_count' => count($winners),
                'total_distributed' => $distribution['distribution_pool']
            ]);

            return [
                'winners' => $winners,
                'total_distributed' => $distribution['distribution_pool'],
                'admin_commission' => $distribution['admin_commission'],
                'recipients' => $distribution['recipients'],
            ];
        });
    }

    /**
     * Calculate distribution based on participants and pool
     * Based on the redistribution table rules
     */
    protected function calculateDistribution(int $participants, float $totalPool): array
    {
        // 10% admin commission
        $adminCommissionRate = 0.10;
        $adminCommission = $totalPool * $adminCommissionRate;
        $distributionPool = $totalPool - $adminCommission;

        // Determine recipients based on participant ranges from table
        $recipients = 10; // Default minimum

        if ($participants >= 4000 && $participants < 5000) {
            $recipients = 10;
        } elseif ($participants >= 5000 && $participants < 6000) {
            $recipients = 12;
        } elseif ($participants >= 6000 && $participants < 7000) {
            $recipients = 15;
        } elseif ($participants >= 7000 && $participants < 8000) {
            $recipients = 17;
        } elseif ($participants >= 8000 && $participants < 9000) {
            $recipients = 20;
        } elseif ($participants >= 9000 && $participants < 10000) {
            $recipients = 22;
        } elseif ($participants >= 10000) {
            $recipients = 25;
        }

        return [
            'recipients' => $recipients,
            'distribution_pool' => $distributionPool,
            'admin_commission' => $adminCommission,
            'amount_per_recipient' => $distributionPool / $recipients,
        ];
    }

    /**
     * Get time remaining for draw
     */
    protected function getTimeRemaining(WeeklyDraw $draw): array
    {
        $now = Carbon::now();
        $targetTime = $draw->status === 'active' ? $draw->countdown_ends_at : $draw->claim_deadline;

        if ($now->greaterThan($targetTime)) {
            return [
                'days' => 0,
                'hours' => 0,
                'minutes' => 0,
                'seconds' => 0,
                'expired' => true,
            ];
        }

        $diff = $now->diff($targetTime);

        return [
            'days' => $diff->d,
            'hours' => $diff->h,
            'minutes' => $diff->i,
            'seconds' => $diff->s,
            'expired' => false,
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
        $totalDonations = Donation::where('stripe_payment_status', 'completed')->sum('amount');
        $totalParticipants = Donation::where('stripe_payment_status', 'completed')
            ->distinct('user_id')
            ->count();
        $totalWinners = DrawWinner::count();
        $totalDistributed = DrawWinner::sum('amount_won');
        $totalCommission = WeeklyDraw::sum('admin_commission');

        return [
            'total_donations' => $totalDonations,
            'total_participants' => $totalParticipants,
            'total_winners' => $totalWinners,
            'total_distributed' => $totalDistributed,
            'total_commission' => $totalCommission,
            'active_draws' => WeeklyDraw::where('status', 'active')->count(),
        ];
    }
}
