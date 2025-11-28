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
        // Use centralized timezone from config
        $now = Carbon::now(config('app.timezone'));

        Log::info('Draw automation check', [
            'day' => $now->format('l'),
            'time' => $now->format('H:i:s'),
            'hour' => $now->hour,
            'minute' => $now->minute,
            'timezone' => config('app.timezone'),
        ]);

        // PRODUCTION: Monday 12:00 AM - Create New Draw
        // if ($now->isMonday() && $now->hour === 0 && $now->minute === 0) {
        //     Log::info('Triggering new draw creation (Monday 12:00 AM)');
        //     $this->createNewDraw();
        // }

        // PRODUCTION: Sunday 5:00 PM - Finalize & Select Winners
        // if ($now->isSunday() && $now->hour === 17 && $now->minute === 0) {
        //     Log::info('Triggering draw finalization (Sunday 5:00 PM)');
        //     $this->finalizeAndSelectWinners();
        // }

        // TESTING MODE (UNCOMMENT FOR TESTING)

        // TEST: Wednesday 6:33 PM - Create New Draw
        if ($now->isFriday() && $now->hour === 11 && $now->minute === 52) {
            Log::info('TEST: Triggering new draw creation');
            $this->createNewDraw();
        }

        // TEST: Tuesday 2:27 PM - Finalize & Select Winners
        if ($now->isFriday() && $now->hour === 11 && $now->minute === 57) {
            Log::info('TEST: Triggering draw finalization');
            $this->finalizeAndSelectWinners();
        }
        return 0;
    }



    // create new draw function
    protected function createNewDraw()
    {
        try {
            // Check if active draw already exists
            $existingDraw = WeeklyDraw::where('status', 'active')->first();

            if ($existingDraw) {
                $this->error('Active draw already exists. Skipping creation.');
                Log::warning('Active draw already exists', [
                    'existing_draw_id' => $existingDraw->id,
                    'week_number' => $existingDraw->week_number
                ]);
                return;
            }

            // Create new draw
            $draw = $this->weeklyDrawService->createNewDraw();

            $this->info("✅ New draw created: Week #{$draw->week_number}");
            Log::info('Weekly draw created successfully', [
                'week_number' => $draw->week_number,
                'draw_id' => $draw->id,
                'start' => $draw->start_date,
                'end' => $draw->end_date,
                'timezone' => config('app.timezone'),
            ]);
        } catch (\Exception $e) {
            $this->error('❌ Failed to create draw: ' . $e->getMessage());
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
            // Find active draw
            $activeDraw = WeeklyDraw::where('status', 'active')->first();

            if (!$activeDraw) {
                $this->error('No active draw found to finalize');
                Log::warning('No active draw found on Sunday 5 PM');
                return;
            }

            // Step 1: Finalize Draw (Change status to 'claiming')
            $draw = $this->weeklyDrawService->finalizeDraw($activeDraw->id);
            $this->info("Draw finalized: Week #{$draw->week_number}");

            // Step 2: Select Winners
            $result = $this->weeklyDrawService->selectWinners($draw->id);

            $this->info("Winners selected successfully!");
            $this->info("   - Winners: {$result['recipients']}");
            $this->info("   - Total distributed: $" . number_format($result['total_distributed'], 2));
            $this->info("   - Admin commission: $" . number_format($result['admin_commission'], 2));

            Log::info('Draw completed successfully', [
                'week_number' => $draw->week_number,
                'winners_count' => $result['recipients'],
                'total_distributed' => $result['total_distributed'],
                'admin_commission' => $result['admin_commission'],
                'timezone' => config('app.timezone'),
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
