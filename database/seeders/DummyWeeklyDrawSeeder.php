<?php

namespace Database\Seeders;

use App\Models\WeeklyDraw;
use App\Models\DrawAutomateSetting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DummyWeeklyDrawSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure DrawAutomateSettings exist
        DrawAutomateSetting::firstOrCreate([], [
            'draw_start_day' => 'Monday',
            'draw_start_time' => '00:00:00',
            'draw_end_day' => 'Sunday',
            'draw_end_time' => '17:00:00',
        ]);

        $now = Carbon::now();

        // 2. Create a "completed" draw for last week
        WeeklyDraw::create([
            'week_number' => $now->copy()->subWeek()->weekOfYear,
            'year' => $now->copy()->subWeek()->year,
            'start_date' => $now->copy()->subDays(14)->startOfDay(),
            'end_date' => $now->copy()->subDays(7)->endOfDay(),
            'countdown_ends_at' => $now->copy()->subDays(7)->endOfDay(),
            'claim_deadline' => $now->copy()->subDays(4)->endOfDay(),
            'status' => 'completed',
            'total_pool' => 500.00,
            'total_participants' => 50,
            'total_recipients' => 5,
            'admin_commission' => 50.00,
            'winners_selected' => true,
        ]);

        // 3. Create an "active" draw for current week
        WeeklyDraw::create([
            'week_number' => $now->weekOfYear,
            'year' => $now->year,
            'start_date' => $now->copy()->startOfWeek(),
            'end_date' => $now->copy()->endOfWeek(),
            'countdown_ends_at' => $now->copy()->endOfWeek(),
            'claim_deadline' => $now->copy()->addDays(3)->endOfDay(),
            'status' => 'active',
            'total_pool' => 120.50,
            'total_participants' => 15,
            'total_recipients' => 0,
            'admin_commission' => 0.00,
            'winners_selected' => false,
        ]);

        $this->command->info('Dummy Weekly Draws and Settings seeded successfully!');
    }
}
