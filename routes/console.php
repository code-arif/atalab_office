<?php

use App\Models\OtpLog;
use App\Models\Donation;
use App\Models\DrawWinner;
use App\Models\WeeklyDraw;
use App\Models\UserSession;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

// ============================================
// AUTOMATED WEEKLY DRAW SCHEDULING (EVERY MINUTE CHECK)
// ============================================

// ============================================
// CENTRALIZED TIMEZONE (FROM CONFIG)
// ============================================
$timezone = config('app.timezone', 'Asia/Dhaka');

/**
 * Run automation check every minute
 * More reliable for critical operations
 */
Schedule::command('draw:automate')
    ->everyMinute()
    ->timezone($timezone)
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
    ->timezone($timezone)
    ->runInBackground()
    ->onSuccess(function () {
        Log::info('New draw created (Monday 12 AM)');
    });
*/

// Finalize draw every Sunday at 5:00 PM
/*
Schedule::command('draw:automate')
    ->weeklyOn(0, '17:00') // 0 = Sunday
    ->timezone($timezone)
    ->runInBackground()
    ->onSuccess(function () {
        Log::info('Draw finalized (Sunday 5 PM)');
    });
*/

// ============================================
// CLEANUP TASKS
// ============================================

/**
 * Clean up expired pending donations
 * Runs daily at 2:00 AM
 */
Schedule::call(function () {
    $deleted = Donation::where('stripe_payment_status', 'pending')
        ->where('created_at', '<', now(config('app.timezone'))->subHours(24))
        ->delete();

    if ($deleted > 0) {
        Log::info("Cleaned up {$deleted} expired pending donations");
    }
})
    ->dailyAt('02:00')
    ->timezone($timezone)
    ->name('cleanup-pending-donations');

/**
 * Clean up expired user sessions
 * Runs every hour
 */
Schedule::call(function () {
    $expired = UserSession::where('status', 'pending_donation')
        ->where('expires_at', '<', now(config('app.timezone')))
        ->update(['status' => 'expired']);

    if ($expired > 0) {
        Log::info("Expired {$expired} user sessions");
    }
})
    ->hourly()
    ->timezone($timezone)
    ->name('cleanup-expired-sessions');

/**
 * Clean up expired OTP codes
 * Runs every 15 minutes
 */
Schedule::call(function () {
    $expired = OtpLog::where('status', 'sent')
        ->where('expires_at', '<', now(config('app.timezone')))
        ->update(['status' => 'expired']);

    if ($expired > 0) {
        Log::info("Expired {$expired} OTP codes");
    }
})
    ->everyFifteenMinutes()
    ->timezone($timezone)
    ->name('cleanup-expired-otps');

/**
 * Delete old OTP logs (older than 30 days)
 * Runs daily at 3:00 AM
 */
Schedule::call(function () {
    $deleted = OtpLog::where('created_at', '<', now(config('app.timezone'))->subDays(30))
        ->delete();

    if ($deleted > 0) {
        Log::info("Deleted {$deleted} old OTP logs");
    }
})
    ->dailyAt('03:00')
    ->timezone($timezone)
    ->name('cleanup-old-otp-logs');

/**
 * Generate daily statistics report
 * Runs daily at 11:59 PM
 */
Schedule::call(function () use ($timezone) {
    $stats = [
        'date' => now($timezone)->toDateString(),
        'total_donations_today' => Donation::whereDate('created_at', today($timezone))
            ->where('stripe_payment_status', 'completed')
            ->sum('amount'),
        'total_participants_today' => Donation::whereDate('created_at', today($timezone))
            ->where('stripe_payment_status', 'completed')
            ->distinct('user_id')
            ->count(),
        'new_registrations_today' => \App\Models\User::whereDate('registered_at', today($timezone))
            ->count(),
        'completed_donations' => Donation::where('stripe_payment_status', 'completed')->count(),
        'pending_donations' => Donation::where('stripe_payment_status', 'pending')->count(),
    ];

    Log::info('Daily statistics report', $stats);
})
    ->dailyAt('23:59')
    ->timezone($timezone)
    ->name('generate-daily-stats');

/**
 * Monitor system health
 * Runs every 5 minutes
 */
Schedule::call(function () use ($timezone) {
    $now = now($timezone);

    // Check if there's an active draw (except Sunday 5 PM - Monday 12 AM)
    $shouldHaveActiveDraw = !($now->isSunday() && $now->hour >= 17)
        && !($now->isMonday() && $now->hour < 0);

    if ($shouldHaveActiveDraw) {
        $activeDraw = WeeklyDraw::where('status', 'active')->first();

        if (!$activeDraw) {
            Log::warning('No active draw found during business hours', [
                'day' => $now->format('l'),
                'time' => $now->format('H:i:s')
            ]);
        }
    }
})
    ->everyFiveMinutes()
    ->timezone($timezone)
    ->name('health-check');
