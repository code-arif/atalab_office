<?php

namespace App\Services;

use App\Models\WeeklyDraw;
use App\Models\Donation;
use App\Models\DrawWinner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DrawService
{
    private const TIERS = [
        ['min' => 4000, 'max' => 4999, 'recipients' => 10, 'per_winner' => 9250, 'odds' => '1 in 400'],
        ['min' => 5000, 'max' => 5999, 'recipients' => 12, 'per_winner' => 9250, 'odds' => '1 in 400'],
        ['min' => 6000, 'max' => 6999, 'recipients' => 15, 'per_winner' => 9250, 'odds' => '1 in 400'],
        ['min' => 7000, 'max' => 7999, 'recipients' => 17, 'per_winner' => 9250, 'odds' => '1 in 400'],
        ['min' => 8000, 'max' => 8999, 'recipients' => 20, 'per_winner' => 9250, 'odds' => '1 in 400'],
        ['min' => 9000, 'max' => 9999, 'recipients' => 22, 'per_winner' => 9250, 'odds' => '1 in 400'],
        ['min' => 10000, 'max' => PHP_INT_MAX, 'recipients' => 25, 'per_winner' => 9250, 'odds' => '1 in 400'],
    ];

    public function getTierByParticipants(int $participants): ?array
    {
        foreach (self::TIERS as $tier) {
            if ($participants >= $tier['min'] && $participants <= $tier['max']) {
                return $tier;
            }
        }
        return null;
    }

    public function selectWinners(WeeklyDraw $draw): bool
    {
        return DB::transaction(function () use ($draw) {
            // Get eligible donations
            $donations = Donation::where('week_id', $draw->id)
                ->where('is_eligible_for_draw', true)
                ->where('stripe_payment_status', 'succeeded')
                ->get();

            $totalParticipants = $donations->count();
            $totalPool = $donations->sum('amount');

            if ($totalParticipants < 4000) {
                Log::info("Not enough participants for draw {$draw->id}. Need 4000, have {$totalParticipants}");
                return false;
            }

            $tier = $this->getTierByParticipants($totalParticipants);

            if (!$tier) {
                Log::error("No tier found for {$totalParticipants} participants");
                return false;
            }

            $numberOfWinners = $tier['recipients'];
            $amountPerWinner = $tier['per_winner'];

            // Calculate admin commission
            $totalPayout = $numberOfWinners * $amountPerWinner;
            $adminCommission = $totalPool - $totalPayout;

            // Randomly select winners
            $selectedWinners = $donations->random(min($numberOfWinners, $totalParticipants));

            foreach ($selectedWinners as $winner) {
                DrawWinner::create([
                    'weekly_draw_id' => $draw->id,
                    'user_id' => $winner->user_id,
                    'donation_id' => $winner->id,
                    'amount_won' => $amountPerWinner,
                    'claimed' => false,
                    'payout_status' => 'pending',
                ]);
            }

            // Update draw
            $draw->update([
                'total_pool' => $totalPool,
                'total_participants' => $totalParticipants,
                'total_recipients' => $numberOfWinners,
                'admin_commission' => $adminCommission,
                'winners_selected' => true,
                'status' => 'claiming',
            ]);

            Log::info("Winners selected for draw {$draw->id}. Pool: {$totalPool}, Winners: {$numberOfWinners}");

            return true;
        });
    }

    public function getCurrentActiveDraw(): ?WeeklyDraw
    {
        return WeeklyDraw::where('status', 'active')
            ->where('countdown_ends_at', '>', now())
            ->first();
    }

    public function getDrawStatistics(WeeklyDraw $draw): array
    {
        $participants = $draw->total_participants;
        $tier = $this->getTierByParticipants($participants);

        return [
            'week_number' => $draw->week_number,
            'total_participants' => $participants,
            'total_pool' => $draw->total_pool,
            'status' => $draw->status,
            'countdown_seconds' => $draw->countdown_seconds,
            'claim_time_left_seconds' => $draw->claim_time_left_seconds,
            'tier' => $tier,
            'is_active' => $draw->isActive(),
            'is_claiming' => $draw->isClaiming(),
            'winners_count' => $draw->winners()->count(),
        ];
    }
}
