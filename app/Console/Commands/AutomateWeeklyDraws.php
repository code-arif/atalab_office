<?php

namespace App\Console\Commands;

use App\Models\WeeklyDraw;
use App\Services\WeeklyDrawService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutomateWeeklyDraws extends Command
{
    protected $signature = 'draw:automate';
    protected $description = 'Automate weekly draw lifecycle: create, finalize, select winners';

    protected $weeklyDrawService;

    // service injection
    public function __construct(WeeklyDrawService $weeklyDrawService)
    {
        parent::__construct();
        $this->weeklyDrawService = $weeklyDrawService;
    }

    // handele create new draw
    public function handle()
    {
        $now = Carbon::now('Asia/Dhaka');

        Log::info('Draw automation check', [
            'day' => $now->format('l'),
            'time' => $now->format('H:i:s'),
            'hour' => $now->hour,
            'minute' => $now->minute,
        ]);

        // TESTING: Tuesday 12:05 (for your current time)
        if ($now->isTuesday() && $now->hour === 14 && $now->minute === 05) {
            Log::info('TEST: Triggering new draw creation');
            $this->createNewDraw();
        }

        // PRODUCTION: Monday 12:00 AM
        // if ($now->isMonday() && $now->hour === 0 && $now->minute === 0) {
        //     Log::info('Triggering new draw creation');
        //     $this->createNewDraw();
        // }

        // TESTING: Every Sunday 5:00 PM - Finalize & Select Winners
        if ($now->isTuesday() && $now->hour === 14 && $now->minute === 27) {
            Log::info('Triggering draw finalization');
            $this->finalizeAndSelectWinners();
        }

        // PRODUCTION: Every Sunday 5:00 PM - Finalize & Select Winners
        // if ($now->isSunday() && $now->hour === 17 && $now->minute === 0) {
        //     Log::info('Triggering draw finalization');
        //     $this->finalizeAndSelectWinners();
        // }

        return 0;
    }


    // create new draw function
    protected function createNewDraw()
    {
        try {
            $existingDraw = WeeklyDraw::where('status', 'active')->first();

            if ($existingDraw) {
                $this->error('Active draw already exists. Skipping creation.');
                Log::warning('Active draw already exists', [
                    'existing_draw_id' => $existingDraw->id
                ]);
                return;
            }

            $draw = $this->weeklyDrawService->createNewDraw();

            $this->info("New draw created: Week #{$draw->week_number}");
            Log::info('Weekly draw created', [
                'week_number' => $draw->week_number,
                'draw_id' => $draw->id,
                'start' => $draw->start_date,
                'end' => $draw->end_date,
            ]);
        } catch (\Exception $e) {
            $this->error('Failed to create draw: ' . $e->getMessage());
            Log::error('Draw creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }


    // winner selection function
    protected function finalizeAndSelectWinners()
    {
        try {
            $activeDraw = WeeklyDraw::where('status', 'active')->first();

            if (!$activeDraw) {
                $this->error('No active draw found to finalize');
                Log::warning('No active draw on Sunday 5 PM');
                return;
            }

            // Step 1: Finalize Draw (Change to 'claiming')
            $draw = $this->weeklyDrawService->finalizeDraw($activeDraw->id);
            $this->info("Draw finalized: Week #{$draw->week_number}");

            // Step 2: Select Winners
            $result = $this->weeklyDrawService->selectWinners($draw->id);

            $this->info("Winners selected: {$result['recipients']} winners");
            $this->info("Total distributed: $" . number_format($result['total_distributed'], 2));
            $this->info("Admin commission: $" . number_format($result['admin_commission'], 2));

            Log::info('Draw completed successfully', [
                'week_number' => $draw->week_number,
                'winners_count' => $result['recipients'],
                'total_distributed' => $result['total_distributed'],
                'admin_commission' => $result['admin_commission'],
            ]);
        } catch (\Exception $e) {
            $this->error('Failed: ' . $e->getMessage());
            Log::error('Finalization/selection failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
