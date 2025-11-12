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

    public function __construct(WeeklyDrawService $weeklyDrawService)
    {
        parent::__construct();
        $this->weeklyDrawService = $weeklyDrawService;
    }

    public function handle()
    {
        // Log::info('hello');
        $now = Carbon::now();
        // Log::info($now);

        // $this->createNewDraw();
        // Log::info('created');

        // Every Monday 12:00 AM - Create New Draw
        if ($now->isSameDay('Monday') && $now->format('H:i') === '16:37') {
            $this->createNewDraw();
            Log::info('New draw creation');
        }

        // Every Sunday 5:00 PM - Finalize & Select Winners
        if ($now->isSameDay('Sunday') && $now->format('H:i') === '17:00') {
            $this->finalizeAndSelectWinners();
        }

        return 0;
    }

    protected function createNewDraw()
    {
        try {
            $existingDraw = WeeklyDraw::where('status', 'active')->first();

            if ($existingDraw) {
                $this->error('Active draw already exists. Skipping creation.');
                Log::warning('Attempted to create draw but active draw exists', [
                    'existing_draw_id' => $existingDraw->id
                ]);
                return;
            }

            $draw = $this->weeklyDrawService->createNewDraw();

            $this->info("✓ New draw created: Week #{$draw->week_number}");
            Log::info('Weekly draw created automatically', [
                'week_number' => $draw->week_number,
                'draw_id' => $draw->id
            ]);
        } catch (\Exception $e) {
            $this->error('Failed to create draw: ' . $e->getMessage());
            Log::error('Automated draw creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function finalizeAndSelectWinners()
    {
        try {
            $activeDraw = WeeklyDraw::where('status', 'active')->first();

            if (!$activeDraw) {
                $this->error('No active draw found to finalize');
                Log::warning('No active draw found on Sunday 5 PM');
                return;
            }

            // Step 1: Finalize Draw (Change status to 'claiming')
            $draw = $this->weeklyDrawService->finalizeDraw($activeDraw->id);
            $this->info("✓ Draw finalized: Week #{$draw->week_number}");

            // Step 2: Select Winners Automatically
            $result = $this->weeklyDrawService->selectWinners($draw->id);

            $this->info("✓ Winners selected: {$result['recipients']} winners");
            $this->info("  Total distributed: $" . number_format($result['total_distributed'], 2));
            $this->info("  Admin commission: $" . number_format($result['admin_commission'], 2));

            Log::info('Draw finalized and winners selected', [
                'week_number' => $draw->week_number,
                'winners_count' => $result['recipients'],
                'total_distributed' => $result['total_distributed']
            ]);
        } catch (\Exception $e) {
            $this->error('Failed to finalize/select winners: ' . $e->getMessage());
            Log::error('Automated finalization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
