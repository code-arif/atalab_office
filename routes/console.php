<?php

use App\Models\Donation;
use App\Models\DrawWinner;
use App\Models\WeeklyDraw;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// ============================================
// AUTOMATED WEEKLY DRAW SCHEDULING (EVERY MINUTE CHECK)
// ============================================

/**
 * Run automation check every minute
 * More reliable for critical operations
 */
Schedule::command('draw:automate')
    ->everyMinute()
    ->timezone('Asia/Dhaka')
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        Log::info('Draw automation check completed');
    })
    ->onFailure(function () {
        Log::error('Draw automation check failed');
    });

// ============================================
// ALTERNATIVE: SPECIFIC TIMING (More Efficient)
// ============================================

/**
 * Uncomment these if you want specific scheduling instead of every minute
 * NOTE: Both approaches will work, but specific timing is more efficient
 */

// Create new draw every Monday at 12:00 AM
/*
Schedule::command('draw:automate')
    ->weeklyOn(1, '00:00') // 1 = Monday
    ->timezone('Asia/Dhaka')
    ->runInBackground()
    ->onSuccess(function () {
        Log::info('New draw created (Monday 12 AM)');
    });
*/

// Finalize draw every Sunday at 5:00 PM
/*
Schedule::command('draw:automate')
    ->weeklyOn(0, '17:00') // 0 = Sunday
    ->timezone('Asia/Dhaka')
    ->runInBackground()
    ->onSuccess(function () {
        Log::info('Draw finalized (Sunday 5 PM)');
    });
*/

// ============================================
// MAINTENANCE & CLEANUP TASKS
// ============================================

/**
 * Clean up expired pending donations
 * Runs daily at 2:00 AM
 */
Schedule::call(function () {
    $deleted = Donation::where('stripe_payment_status', 'pending')
        ->where('created_at', '<', now()->subHours(24))
        ->delete();

    if ($deleted > 0) {
        Log::info("Cleaned up {$deleted} expired pending donations");
    }
})
    ->dailyAt('02:00')
    ->timezone('Asia/Dhaka')
    ->name('cleanup-pending-donations');

/**
 * Clean up duplicate donations
 * Runs daily at 3:00 AM
 */
Schedule::call(function () {
    $duplicates = Donation::where('stripe_payment_status', 'duplicate')
        ->where('created_at', '<', now()->subDays(7))
        ->get();

    foreach ($duplicates as $donation) {
        // Optional: Initiate refund via Stripe
        Log::info('Duplicate donation found (requires refund)', [
            'donation_id' => $donation->id,
            'user_email' => $donation->user->email ?? 'N/A',
        ]);
    }
})
    ->dailyAt('03:00')
    ->timezone('Asia/Dhaka')
    ->name('handle-duplicate-donations');

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
        Log::info('Reminder needed for winner', [
            'winner_id' => $winner->id,
            'user_email' => $winner->user->email,
            'amount' => $winner->amount_won,
        ]);
    }
})
    ->dailyAt('10:00')
    ->timezone('Asia/Dhaka')
    ->name('send-winner-reminders');

/**
 * Auto-complete draws past claim deadline
 * Runs every hour
 */
Schedule::call(function () {
    $expiredDraws = WeeklyDraw::where('status', 'claiming')
        ->where('claim_deadline', '<', now())
        ->get();

    foreach ($expiredDraws as $draw) {
        $draw->update(['status' => 'completed']);

        Log::info('Auto-completed expired draw', [
            'week_number' => $draw->week_number,
            'draw_id' => $draw->id,
        ]);
    }
})
    ->hourly()
    ->timezone('Asia/Dhaka')
    ->name('auto-complete-expired-draws');

/**
 * Generate daily statistics report
 * Runs daily at 11:59 PM
 */
Schedule::call(function () {
    $stats = [
        'date' => now('Asia/Dhaka')->toDateString(),
        'total_donations_today' => Donation::whereDate('created_at', today('Asia/Dhaka'))
            ->where('stripe_payment_status', 'completed')
            ->sum('amount'),
        'total_participants_today' => Donation::whereDate('created_at', today('Asia/Dhaka'))
            ->where('stripe_payment_status', 'completed')
            ->distinct('user_id')
            ->count(),
        'active_draws' => WeeklyDraw::where('status', 'active')->count(),
        'completed_donations' => Donation::where('stripe_payment_status', 'completed')->count(),
        'pending_donations' => Donation::where('stripe_payment_status', 'pending')->count(),
    ];

    Log::info('Daily statistics report', $stats);

    // TODO: Send to admin dashboard or email
})
    ->dailyAt('23:59')
    ->timezone('Asia/Dhaka')
    ->name('generate-daily-stats');

/**
 * Monitor system health
 * Runs every 5 minutes
 */
Schedule::call(function () {
    $activeDraw = WeeklyDraw::where('status', 'active')->first();

    if (!$activeDraw && now('Asia/Dhaka')->dayOfWeek !== 0 && now('Asia/Dhaka')->hour !== 17) {
        Log::warning('No active draw found during business hours');
    }
})
    ->everyFiveMinutes()
    ->timezone('Asia/Dhaka')
    ->name('health-check');
