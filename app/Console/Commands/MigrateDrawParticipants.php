<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WeeklyDraw;
use App\Models\Donation;
use App\Models\DrawParticipant;
use App\Models\DrawWinner;

class MigrateDrawParticipants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'draw:migrate-participants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfills the new DrawParticipant table based on historical donations to preserve exact snapshot logic.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting DrawParticipant backfill migration...');
        DrawParticipant::truncate(); // Start clean

        $draws = WeeklyDraw::orderBy('id', 'asc')->get();

        foreach ($draws as $draw) {
            $this->info("Processing Weekly Draw #{$draw->id} (Week {$draw->week_number})");
            
            // 1. Rollover participants from previous draw
            $previousDraw = WeeklyDraw::where('id', '<', $draw->id)
                ->orderBy('id', 'desc')
                ->first();

            $rolloverCount = 0;

            if ($previousDraw) {
                // Anyone from previous draw who hasn't won in the last 6/12 months (or simply didn't win that draw for historical simplicity)
                // Actually, just anyone who didn't win in the previous draw is rolled over!
                $previousParticipants = DrawParticipant::where('weekly_draw_id', $previousDraw->id)->get();
                $previousWinners = DrawWinner::where('weekly_draw_id', $previousDraw->id)->pluck('user_id')->toArray();

                foreach ($previousParticipants as $participant) {
                    if (!in_array($participant->user_id, $previousWinners)) {
                        // Check if they won recently in ANY draw before this one
                        $hasWonRecently = DrawWinner::where('user_id', $participant->user_id)
                            ->where('weekly_draw_id', '<=', $previousDraw->id)
                            ->exists(); // Simplification: we exclude anyone who won previously in this backfill

                        if (!$hasWonRecently) {
                            DrawParticipant::create([
                                'weekly_draw_id' => $draw->id,
                                'user_id' => $participant->user_id,
                                'donation_id' => $participant->donation_id,
                                'is_rollover' => true,
                            ]);
                            $rolloverCount++;
                        }
                    }
                }
            }

            // 2. New Participants for current draw
            $newDonations = Donation::where('week_id', $draw->id)
                ->where('stripe_payment_status', 'completed')
                ->where('is_eligible_for_draw', true)
                ->get();

            $newCount = 0;
            foreach ($newDonations as $donation) {
                $hasWonRecently = DrawWinner::where('user_id', $donation->user_id)
                    ->where('weekly_draw_id', '<', $draw->id)
                    ->exists();

                if (!$hasWonRecently) {
                    DrawParticipant::firstOrCreate([
                        'weekly_draw_id' => $draw->id,
                        'user_id' => $donation->user_id,
                    ], [
                        'donation_id' => $donation->id,
                        'is_rollover' => false,
                    ]);
                    $newCount++;
                }
            }

            $totalParticipants = DrawParticipant::where('weekly_draw_id', $draw->id)->count();

            // Update total_participants if not completed? 
            // We want historical accuracy, so let's update it.
            $draw->update(['total_participants' => $totalParticipants]);

            $this->info("   - Rollovers: {$rolloverCount}");
            $this->info("   - New: {$newCount}");
            $this->info("   - Total: {$totalParticipants}");
        }

        $this->info('Migration completed successfully!');
    }
}
