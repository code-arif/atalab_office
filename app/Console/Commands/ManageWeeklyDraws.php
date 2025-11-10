<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WeeklyDraw;
use App\Services\DrawService;
use Carbon\Carbon;

class ManageWeeklyDraws extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'draws:manage';

    public function __construct(private DrawService $drawService)
    {
        parent::__construct();
    }

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage weekly draws lifecycle';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        // Check current draw
        $currentDraw = WeeklyDraw::where('status', 'active')->first();

        // If countdown ended, select winners
        if ($currentDraw && $now->gte($currentDraw->countdown_ends_at)) {
            $this->info("Countdown ended for week {$currentDraw->week_number}. Selecting winners...");

            if ($this->drawService->selectWinners($currentDraw)) {
                $this->info("Winners selected successfully!");
            } else {
                $this->error("Failed to select winners or not enough participants");
            }
        }
        // Check if claiming period ended
        $claimingDraw = WeeklyDraw::where('status', 'claiming')->first();

        if ($claimingDraw && $now->gte($claimingDraw->claim_deadline)) {
            $this->info("Claim period ended for week {$claimingDraw->week_number}. Completing draw...");

            $claimingDraw->update(['status' => 'completed']);

            // Handle unclaimed winnings (optional - send notification or forfeit)
            $this->handleUnclaimedWinnings($claimingDraw);

            $this->info("Draw {$claimingDraw->week_number} completed!");
        }

        // Create new draw if needed (no active draw exists)
        if (!WeeklyDraw::where('status', 'active')->exists()) {
            $this->createNewDraw();
        }

        $this->info("Draw management completed successfully!");
    }


    private function createNewDraw()
    {
        $lastDraw = WeeklyDraw::orderBy('week_number', 'desc')->first();
        $nextWeekNumber = $lastDraw ? $lastDraw->week_number + 1 : 1;

        $startDate = now();
        $endDate = $startDate->copy()->addDays(7);
        $countdownEnds = $endDate;
        $claimDeadline = $endDate->copy()->addHours(12);

        $newDraw = WeeklyDraw::create([
            'week_number' => $nextWeekNumber,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'countdown_ends_at' => $countdownEnds,
            'claim_deadline' => $claimDeadline,
            'status' => 'active',
        ]);

        $this->info("New draw created: Week {$nextWeekNumber}");
    }

    private function handleUnclaimedWinnings(WeeklyDraw $draw)
    {
        $unclaimedWinners = $draw->winners()->where('claimed', false)->get();

        foreach ($unclaimedWinners as $winner) {
            // Option 1: Send final notification
            // Notification::send($winner->user, new UnclaimedWinningNotification($winner));

            // Option 2: Mark as forfeited
            $winner->update([
                'payout_status' => 'forfeited',
            ]);

            $this->warn("User {$winner->user_id} forfeited ${$winner->amount_won}");
        }

        $this->info("Processed {$unclaimedWinners->count()} unclaimed winnings");
    }
}
