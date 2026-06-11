<?php

namespace App\Services;

use Exception;
use App\Models\Donation;
use App\Models\DrawWinner;
use App\Models\WeeklyDraw;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\WinnerExclusion;
use App\Models\DrawCycle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Models\DrawAutomateSetting;

class WeeklyDrawService
{
    /**
     * Get the dynamic automation settings from the database.
     */
    protected function getSettings(): DrawAutomateSetting
    {
        return DrawAutomateSetting::firstOrCreate([], [
            'admin_fee_percentage' => 10,
            'odds_ratio' => 250,
            'minimum_participants' => 100,
            'winner_exclusion_months' => 12,
        ]);
    }

    /**
     * Get current active draw
     */
    public function getCurrentDraw(): WeeklyDraw
    {
        $draw = WeeklyDraw::where('status', 'active')
            ->orderBy('week_number', 'desc')
            ->first();

        if (!$draw) {
            throw new Exception('No active draw available');
        }

        return $draw;
    }

    /**
     * UPDATED: Create new weekly draw with ISO week number (resets every year)
     */
    public function createNewDraw(): WeeklyDraw
    {
        return DB::transaction(function () {
            $activeDraw = WeeklyDraw::where('status', 'active')->first();
            if ($activeDraw) {
                throw new Exception('Active draw already exists');
            }

            $settings = $this->getSettings();

            $now = Carbon::now(config('app.timezone'));

            // Map string days ('Monday') to Carbon constants (e.g. Carbon::MONDAY)
            $startDayConstant = constant('\Carbon\Carbon::' . strtoupper($settings->draw_start_day));
            $endDayConstant = constant('\Carbon\Carbon::' . strtoupper($settings->draw_end_day));

            $startTime = Carbon::parse($settings->draw_start_time);
            $endTime = Carbon::parse($settings->draw_end_time);

            $startDate = $now->copy()->startOfWeek($startDayConstant)->setTime($startTime->hour, $startTime->minute, 0);
            $endDate = $startDate->copy()->endOfWeek($endDayConstant)->setTime($endTime->hour, $endTime->minute, 0);
            $countdownEndsAt = $endDate->copy();

            // Add a buffer day for claims (e.g., +1 day at 5:00 AM)
            $claimDeadline = $endDate->copy()->addDay()->setTime(5, 0, 0);

            // Get ISO week number (1-52/53) - resets every year
            $weekNumber = (int) $startDate->isoWeek();
            $year = (int) $startDate->isoWeekYear(); // Use ISO year (handles edge cases)

            // Check if this week already has a draw for this year
            $existingDraw = WeeklyDraw::where('week_number', $weekNumber)
                ->where('year', $year)
                ->first();

            if ($existingDraw) {
                throw new Exception("Draw for Week #{$weekNumber} of year {$year} already exists");
            }

            // Get or create active DrawCycle
            $cycle = DrawCycle::where('status', 'active')->first();
            if (!$cycle) {
                $cycle = DrawCycle::create(['status' => 'active']);
            }

            $draw = WeeklyDraw::create([
                'draw_cycle_id' => $cycle->id,
                'week_number' => $weekNumber,
                'year' => $year, // Store year separately
                'start_date' => $startDate,
                'end_date' => $endDate,
                'countdown_ends_at' => $countdownEndsAt,
                'claim_deadline' => $claimDeadline,
                'status' => 'active',
                'total_pool' => 0,
                'total_participants' => 0,
                'eligible_participants' => 0,
                'total_recipients' => 0,
                'admin_commission' => 0,
                'winners_selected' => false,
            ]);

            // Cleanup expired exclusions
            $this->cleanupExpiredExclusions();

            Log::info('New weekly draw created', [
                'week_number' => $weekNumber,
                'year' => $year,
                'iso_week' => "Week {$weekNumber} of {$year}",
                'start' => $startDate->toDateTimeString(),
                'end' => $endDate->toDateTimeString(),
            ]);

            return $draw;
        });
    }

    /**
     * Finalize draw
     */
    public function finalizeDraw(int $weekId): WeeklyDraw
    {
        return DB::transaction(function () use ($weekId) {
            $draw = WeeklyDraw::findOrFail($weekId);

            if ($draw->status !== 'active') {
                throw new Exception('Draw is not active');
            }

            $draw->update(['status' => 'claiming']);

            return $draw->fresh();
        });
    }

    /**
     * ENHANCED: Select winners with 6-month exclusion
     */
    public function selectWinners(int $weekId): array
    {
        return DB::transaction(function () use ($weekId) {
            $draw = WeeklyDraw::findOrFail($weekId);

            if ($draw->winners_selected) {
                throw new Exception('Winners already selected for this draw');
            }

            // Get the DrawCycle to evaluate rolling donations
            $cycleId = $draw->draw_cycle_id;

            // Calculate totals using the entire active DrawCycle
            $cycleDonationsQuery = Donation::whereHas('weeklyDraw', function ($q) use ($cycleId) {
                $q->where('draw_cycle_id', $cycleId);
            })->where('stripe_payment_status', 'completed');

            $totalPool = (float) $cycleDonationsQuery->sum('amount');
            $totalParticipants = $cycleDonationsQuery->distinct('user_id')->count('user_id');

            $settings = $this->getSettings();

            // Check minimum participants for ROLLOVER
            if ($totalParticipants < $settings->minimum_participants) {
                // Determine rollover path instead of just throwing Exception
                $draw->update([
                    'status' => 'completed',
                    'is_rolled_over' => true,
                    'total_participants' => $totalParticipants, // store rolling count
                    'total_pool' => $totalPool
                ]);

                Log::info('Draw Rolled Over', [
                    'week_id' => $weekId,
                    'week_number' => $draw->week_number,
                    'participants' => $totalParticipants,
                    'minimum_required' => $settings->minimum_participants
                ]);

                return [
                    'rollover' => true,
                    'participants' => $totalParticipants
                ];
            }

            // Calculate winners dynamically
            $numberOfWinners = (int) ceil($totalParticipants / $settings->odds_ratio);

            // Calculate distribution
            $adminCommission = $totalPool * ($settings->admin_fee_percentage / 100);
            $distributionPool = $totalPool - $adminCommission;
            $amountPerWinner = $distributionPool / $numberOfWinners;

            // CRITICAL: Get excluded users (won in last 6 months)
            $excludedUserIds = $this->getExcludedUserIds();

            Log::info('Winner Selection Process', [
                'week_id' => $weekId,
                'draw_cycle_id' => $cycleId,
                'week_number' => $draw->week_number,
                'year' => $draw->year,
                'participants' => $totalParticipants,
                'calculated_winners' => $numberOfWinners,
                'total_pool' => number_format($totalPool, 2),
                'excluded_users' => count($excludedUserIds),
            ]);

            // Get eligible donations across the ENTIRE cycle (excluding recent winners)
            $eligibleDonations = Donation::whereHas('weeklyDraw', function ($q) use ($cycleId) {
                    $q->where('draw_cycle_id', $cycleId);
                })
                ->where('stripe_payment_status', 'completed')
                ->where('is_eligible_for_draw', true)
                ->whereNotIn('user_id', $excludedUserIds)
                ->inRandomOrder()
                ->limit($numberOfWinners * 2) // Get extra for safety
                ->get();

            $eligibleCount = $eligibleDonations->unique('user_id')->count();

            // Check if we have enough eligible participants
            if ($eligibleCount < $numberOfWinners) {
                // Log::error('Not enough eligible participants after exclusion', [
                //     'needed' => $numberOfWinners,
                //     'available' => $eligibleCount,
                //     'excluded' => count($excludedUserIds),
                // ]);

                throw new Exception(
                    "Not enough eligible participants. Need {$numberOfWinners}, found {$eligibleCount} (after 6-month exclusion)"
                );
            }

            // Select unique winners
            $selectedUserIds = [];
            $winners = [];

            foreach ($eligibleDonations as $donation) {
                if (count($selectedUserIds) >= $numberOfWinners) {
                    break;
                }

                // Ensure one entry per user
                if (in_array($donation->user_id, $selectedUserIds)) {
                    continue;
                }

                $selectedUserIds[] = $donation->user_id;

                // Create winner record
                $winner = DrawWinner::create([
                    'weekly_draw_id' => $draw->id,
                    'user_id' => $donation->user_id,
                    'donation_id' => $donation->id,
                    'amount_won' => round($amountPerWinner, 2),
                    'claimed' => false,
                    'payout_status' => 'pending',
                ]);

                // Create 6-month exclusion
                $this->createWinnerExclusion($winner);

                // Update user stats
                $this->updateUserWinStats($donation->user_id, $amountPerWinner);

                $winners[] = $winner;
            }

            // Update draw
            $draw->update([
                'total_pool' => $totalPool,
                'total_participants' => $totalParticipants,
                'eligible_participants' => $eligibleCount,
                'excluded_winners_count' => count($excludedUserIds),
                'total_recipients' => count($winners),
                'admin_commission' => round($adminCommission, 2),
                'winners_selected' => true,
                'status' => 'completed',
                'is_rolled_over' => false,
                'last_stats_update' => now(),
            ]);

            // Close the DrawCycle
            if ($draw->drawCycle) {
                $draw->drawCycle->update(['status' => 'completed']);
            }

            // Clear cache
            Cache::forget('excluded_user_ids');

            // Log::info('Winners selected successfully', [
            //     'week_id' => $weekId,
            //     'week_number' => $draw->week_number,
            //     'year' => $draw->year,
            //     'winners_count' => count($winners),
            //     'total_distributed' => number_format($distributionPool, 2),
            //     'per_winner' => number_format($amountPerWinner, 2),
            // ]);

            return [
                'winners' => $winners,
                'total_distributed' => round($distributionPool, 2),
                'admin_commission' => round($adminCommission, 2),
                'recipients' => count($winners),
                'per_winner' => round($amountPerWinner, 2),
                'excluded_count' => count($excludedUserIds),
            ];
        });
    }

    /**
     * CRITICAL: Get users excluded from winning (won in last 6 months)
     * Uses caching for performance
     */
    protected function getExcludedUserIds(): array
    {
        return Cache::remember('excluded_user_ids', now()->addMinutes(10), function () {
            $settings = $this->getSettings();
            $sixMonthsAgo = Carbon::now()->subMonths($settings->winner_exclusion_months);

            return WinnerExclusion::where('is_active', true)
                ->where('exclusion_ends_at', '>', now())
                ->pluck('user_id')
                ->unique()
                ->toArray();
        });
    }

    /**
     * Create winner exclusion record (6 months from win date)
     */
    protected function createWinnerExclusion(DrawWinner $winner): void
    {
        $settings = $this->getSettings();
        $wonAt = now();
        $exclusionEndsAt = $wonAt->copy()->addMonths($settings->winner_exclusion_months);

        WinnerExclusion::create([
            'user_id' => $winner->user_id,
            'winner_record_id' => $winner->id,
            'won_at' => $wonAt,
            'exclusion_ends_at' => $exclusionEndsAt,
            'is_active' => true,
        ]);

        Log::info('Winner exclusion created', [
            'user_id' => $winner->user_id,
            'winner_id' => $winner->id,
            'exclusion_ends' => $exclusionEndsAt->toDateString(),
        ]);
    }

    /**
     * Update user win statistics
     */
    protected function updateUserWinStats(int $userId, float $amountWon): void
    {
        DB::table('users')
            ->where('id', $userId)
            ->update([
                'times_won' => DB::raw('times_won + 1'),
                'last_won_at' => now(),
            ]);
    }

    /**
     * Cleanup expired exclusions (run daily or before new draw)
     */
    public function cleanupExpiredExclusions(): int
    {
        $cleaned = WinnerExclusion::where('is_active', true)
            ->where('exclusion_ends_at', '<=', now())
            ->update(['is_active' => false]);

        if ($cleaned > 0) {
            Cache::forget('excluded_user_ids');
            Log::info("Cleaned up {$cleaned} expired exclusions");
        }

        return $cleaned;
    }

    /**
     * Get draw statistics with exclusion info
     */
    public function getDrawStats(int $weekId): array
    {
        $settings = $this->getSettings();
        $draw = WeeklyDraw::findOrFail($weekId);

        $expectedWinners = $draw->total_participants > 0
            ? (int) ceil($draw->total_participants / $settings->odds_ratio)
            : 0;

        $excludedCount = count($this->getExcludedUserIds());

        return [
            'week_number' => $draw->week_number,
            'year' => $draw->year,
            'week_display' => "Week {$draw->week_number} of {$draw->year}",
            'status' => $draw->status,
            'total_pool' => $draw->total_pool,
            'total_participants' => $draw->total_participants,
            'eligible_participants' => $draw->eligible_participants,
            'excluded_winners' => $excludedCount,
            'expected_winners' => $expectedWinners,
            'actual_recipients' => $draw->total_recipients,
            'admin_commission' => $draw->admin_commission,
            'countdown_ends_at' => $draw->countdown_ends_at,
            'claim_deadline' => $draw->claim_deadline,
            'odds' => '1:' . $settings->odds_ratio,
            'minimum_participants' => $settings->minimum_participants,
            'exclusion_period_months' => $settings->winner_exclusion_months,
        ];
    }

    /**
     * Check if user is currently excluded from winning
     */
    public function isUserExcluded(int $userId): array
    {
        $exclusion = WinnerExclusion::where('user_id', $userId)
            ->where('is_active', true)
            ->where('exclusion_ends_at', '>', now())
            ->orderBy('exclusion_ends_at', 'desc')
            ->first();

        if (!$exclusion) {
            return [
                'excluded' => false,
                'message' => 'User is eligible to win',
            ];
        }

        return [
            'excluded' => true,
            'exclusion_ends_at' => $exclusion->exclusion_ends_at,
            'days_remaining' => now()->diffInDays($exclusion->exclusion_ends_at),
            'message' => 'User won recently and is excluded until ' . $exclusion->exclusion_ends_at->format('Y-m-d'),
        ];
    }

    /**
     * UPDATED: Get all draws ordered by year and week
     */
    public function getAllDraws(int $page = 1, int $perPage = 20)
    {
        return WeeklyDraw::orderBy('year', 'desc')
            ->orderBy('week_number', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get overview statistics
     */
    public function getOverviewStats(): array
    {
        $settings = $this->getSettings();
        $excludedCount = count($this->getExcludedUserIds());

        return [
            'total_donations' => Donation::where('stripe_payment_status', 'completed')->sum('amount'),
            'total_participants' => Donation::where('stripe_payment_status', 'completed')
                ->distinct('user_id')->count(),
            'total_winners' => DrawWinner::count(),
            'total_distributed' => DrawWinner::sum('amount_won'),
            'total_commission' => WeeklyDraw::sum('admin_commission'),
            'active_draws' => WeeklyDraw::where('status', 'active')->count(),
            'currently_excluded_users' => $excludedCount,
            'current_odds' => '1:' . $settings->odds_ratio,
            'admin_fee_rate' => rtrim(rtrim((string)$settings->admin_fee_percentage, '0'), '.') . '%',
            'exclusion_period' => $settings->winner_exclusion_months . ' months',
        ];
    }

}
