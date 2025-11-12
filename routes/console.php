<?php

use App\Models\Donation;
use App\Models\DrawWinner;
use App\Models\WeeklyDraw;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Artisan commands
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->everyMinute();

// ============================================
// AUTOMATED WEEKLY DRAW SCHEDULING
// ============================================

/**
 * Run automation check every minute
 * The command internally checks if it's the right time
 */
Schedule::command('draw:automate')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        Log::info('Draw automation check completed');
    })
    ->onFailure(function () {
        Log::error('Draw automation check failed');
    });

/**
 * Alternative: Specific timing (More efficient - Choose this if you prefer)
 * Uncomment these and comment out the everyMinute() above
 */

// Create new draw every Monday at 12:00 AM
// Schedule::command('draw:automate')
//     ->weeklyOn(1, '00:00') // 1 = Monday
//     // ->timezone('America/New_York') // Set your timezone
//     ->timezone('Asia/Dhaka')
//     ->runInBackground();

// Finalize draw and select winners every Sunday at 5:00 PM
// Schedule::command('draw:automate')
//     ->weeklyOn(0, '17:00') // 0 = Sunday
//     // ->timezone('America/New_York')
//     ->timezone('Asia/Dhaka')
//     ->runInBackground();

// ============================================
// MAINTENANCE & CLEANUP TASKS
// ============================================

/**
 * Clean up expired pending donations (Security measure)
 * Runs daily at 2:00 AM
 */
Schedule::call(function () {
    $deleted = Donation::where('stripe_payment_status', 'pending')
        ->where('created_at', '<', now()->subHours(24))
        ->delete();

    if ($deleted > 0) {
        Log::info("Cleaned up {$deleted} expired pending donations");
    }
})->dailyAt('02:00');

/**
 * Send reminder emails to unclaimed winners
 * Runs daily at 10:00 AM
 */
Schedule::call(function () {
    $unclaimedWinners = DrawWinner::where('claimed', false)
        ->whereBetween('created_at', [now()->subDays(7), now()->subDays(3)])
        ->with('user')
        ->get();

    foreach ($unclaimedWinners as $winner) {
        // TODO: Implement email sending
        // Mail::to($winner->user->email)->send(new WinnerReminderMail($winner));
        Log::info("Reminder needed for winner", [
            'winner_id' => $winner->id,
            'user_email' => $winner->user->email
        ]);
    }
})->dailyAt('10:00');

/**
 * Auto-complete draws that are past claim deadline
 * Runs every hour
 */
Schedule::call(function () {
    $expiredDraws = WeeklyDraw::where('status', 'claiming')
        ->where('claim_deadline', '<', now())
        ->get();

    foreach ($expiredDraws as $draw) {
        $draw->update(['status' => 'completed']);
        Log::info("Auto-completed expired draw", [
            'week_number' => $draw->week_number,
            'draw_id' => $draw->id
        ]);
    }
})->hourly();

/**
 * Generate daily statistics report
 * Runs daily at 11:59 PM
 */
Schedule::call(function () {
    $stats = [
        'date' => now()->toDateString(),
        'total_donations_today' => Donation::whereDate('created_at', today())
            ->where('stripe_payment_status', 'completed')
            ->sum('amount'),
        'total_participants_today' => Donation::whereDate('created_at', today())
            ->where('stripe_payment_status', 'completed')
            ->distinct('user_id')
            ->count(),
        'active_draws' => WeeklyDraw::where('status', 'active')->count(),
    ];

    Log::info('Daily statistics', $stats);

    // TODO: Send to admin dashboard or email
})->dailyAt('23:59');
