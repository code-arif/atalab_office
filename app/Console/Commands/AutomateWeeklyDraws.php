<?php

namespace App\Console\Commands;

use App\Models\DrawAutomateSetting;
use App\Models\WeeklyDraw;
use App\Services\WeeklyDrawService;
use Carbon\Carbon;
use Exception;
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

        // Retrieve settings, with database fallbacks
        // $settings = DrawAutomateSetting::firstOrCreate([], [
        //     'draw_start_day' => 'Monday',
        //     'draw_start_time' => '00:00:00',
        //     'draw_end_day' => 'Sunday',
        //     'draw_end_time' => '17:00:00',
        // ]);

        $settings = DrawAutomateSetting::firstOrCreate([], [
            'draw_start_day' => 'Monday',
            'draw_start_time' => '00:00:00',
            'draw_end_day' => 'Tuesday',
            'draw_end_time' => '11:20:00',
        ]);

        $startTime = Carbon::parse($settings->draw_start_time);
        $endTime = Carbon::parse($settings->draw_end_time);

        // PRODUCTION: Dynamic Create New Draw
        if ($now->isDayOfWeek($settings->draw_start_day) && $now->hour === $startTime->hour && $now->minute === $startTime->minute) {
            Log::info("Triggering new draw creation ({$settings->draw_start_day} {$settings->draw_start_time})");
            $this->createNewDraw();
        }

        // PRODUCTION: Dynamic Finalize & Select Winners
        if ($now->isDayOfWeek($settings->draw_end_day) && $now->hour === $endTime->hour && $now->minute === $endTime->minute) {
            Log::info("Triggering draw finalization ({$settings->draw_end_day} {$settings->draw_end_time})");
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
                return;
            }

            // Create new draw
            $draw = $this->weeklyDrawService->createNewDraw();

            $this->info("New draw created: Week #{$draw->week_number}");
        } catch (Exception $e) {
            $this->error('Failed to create draw: ' . $e->getMessage());
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

            if (isset($result['rollover']) && $result['rollover']) {
                $this->info("Draw rolled over due to insufficient participants ({$result['participants']}).");
                return;
            }

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
        } catch (Exception $e) {
            $this->error('Failed: ' . $e->getMessage());
            Log::error('Finalization/selection failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
