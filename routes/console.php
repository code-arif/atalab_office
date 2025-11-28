<?php

use App\Models\OtpLog;
use App\Models\Donation;
use App\Models\WinnerExclusion;
use Illuminate\Support\Facades\DB;
use App\Services\WeeklyDrawService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/**
 * ==================================================
 * Laravel 11 Console Routes & Scheduler
 * File: routes/console.php
 * ==================================================
 */

$timezone = config('app.timezone', 'Asia/Dhaka');

/**
 * ==================================================
 * CRITICAL: AUTOMATED DRAW MANAGEMENT
 * ==================================================
 */

// Run automation check every minute for reliability
Schedule::command('draw:automate')
    ->everyMinute()
    ->timezone($timezone)
    ->withoutOverlapping(5) // Prevent overlapping executions
    ->runInBackground()
    ->onSuccess(function () {
        Log::info('Draw automation check completed');
    })
    ->onFailure(function () {
        Log::error('Draw automation check failed');
        // Send alert to admin (implement your notification logic)
    });

/**
 * ==================================================
 * WINNER EXCLUSION CLEANUP
 * ==================================================
 */

// Clean up expired winner exclusions - Daily at 1:00 AM
    Schedule::call(function () {
        $service = app(WeeklyDrawService::class);
        $cleaned = $service->cleanupExpiredExclusions();

        Log::info('Winner exclusions cleaned', ['count' => $cleaned]);
    })
    ->dailyAt('01:00')
    ->timezone(config('app.timezone'))
    ->name('cleanup-winner-exclusions');



/**
 * ==================================================
 * DATABASE CLEANUP TASKS
 * ==================================================
 */

// Clean up expired pending donations - Every 6 hours
    Schedule::call(function () {
        $deleted = Donation::where('stripe_payment_status', 'pending')
            ->where('created_at', '<', now(config('app.timezone'))->subHours(24))
            ->delete();

        if ($deleted > 0) {
            Log::info("🧹 Cleaned {$deleted} expired pending donations");
        }
    })
    ->everySixHours()
    ->timezone(config('app.timezone'))
    ->name('cleanup-pending-donations');



// Clean up expired OTP codes - Every 30 minutes
Schedule::call(function () {
    $expired = OtpLog::where('status', 'sent')
        ->where('expires_at', '<', now(config('app.timezone')))
        ->update(['status' => 'expired']);

    if ($expired > 0) {
        Log::info("🧹 Expired {$expired} OTP codes");
    }
})
    ->everyThirtyMinutes()
    ->timezone(config('app.timezone'))
    ->name('cleanup-expired-otps');

    

// Delete old OTP logs (30+ days) - Daily at 3:00 AM
Schedule::call(function () {
    $deleted = OtpLog::where('created_at', '<', now(config('app.timezone'))->subDays(30))
        ->delete();

    if ($deleted > 0) {
        Log::info("🗑️ Deleted {$deleted} old OTP logs");
    }
})
    ->dailyAt('03:00')
    ->timezone(config('app.timezone'))
    ->name('cleanup-old-otp-logs');

/**
 * ==================================================
 * PERFORMANCE OPTIMIZATION
 * ==================================================
 */

// Optimize database tables - Weekly on Sunday at 4:00 AM
Schedule::call(function () {
    try {
        DB::statement('OPTIMIZE TABLE users, donations, weekly_draws, draw_winners, winner_exclusions, user_week_participations');
        Log::info('✨ Database tables optimized');
    } catch (\Exception $e) {
        Log::error('Database optimization failed: ' . $e->getMessage());
    }
})
    ->weeklyOn(0, '04:00') // Sunday
    ->timezone(config('app.timezone'))
    ->name('optimize-database');

// Clear old cache entries - Daily at 2:00 AM
Schedule::call(function () {
    try {
        \Illuminate\Support\Facades\Cache::forget('excluded_user_ids');
        Log::info('🧹 Critical cache cleared');
    } catch (\Exception $e) {
        Log::error('Cache clear failed: ' . $e->getMessage());
    }
})
    ->dailyAt('02:00')
    ->timezone(config('app.timezone'))
    ->name('clear-critical-cache');

/**
 * ==================================================
 * STATISTICS & REPORTING
 * ==================================================
 */

// Generate daily statistics - Daily at 11:55 PM
Schedule::call(function () {
    $today = today(config('app.timezone'));

    $stats = [
        'date' => $today->toDateString(),
        'donations_today' => Donation::whereDate('created_at', $today)
            ->where('stripe_payment_status', 'completed')
            ->sum('amount'),
        'participants_today' => Donation::whereDate('created_at', $today)
            ->where('stripe_payment_status', 'completed')
            ->distinct('user_id')
            ->count(),
        'new_registrations' => \App\Models\User::whereDate('registered_at', $today)->count(),
        'total_donations' => Donation::where('stripe_payment_status', 'completed')->sum('amount'),
        'total_participants' => Donation::where('stripe_payment_status', 'completed')
            ->distinct('user_id')->count(),
        'active_exclusions' => WinnerExclusion::where('is_active', true)
            ->where('exclusion_ends_at', '>', now())
            ->count(),
    ];

    Log::info('📊 Daily Statistics', $stats);

    // Optional: Store in database or send email report
    // Mail::to(config('admin.email'))->send(new DailyStatsReport($stats));
})
    ->dailyAt('23:55')
    ->timezone(config('app.timezone'))
    ->name('daily-statistics');

/**
 * ==================================================
 * HEALTH MONITORING
 * ==================================================
 */

// System health check - Every 5 minutes
Schedule::call(function () {
    $now = now(config('app.timezone'));

    // Check 1: Active draw exists (except Sunday 5 PM - Monday 12 AM)
    $shouldHaveActiveDraw = !($now->isSunday() && $now->hour >= 17)
        && !($now->isMonday() && $now->hour < 0);

    if ($shouldHaveActiveDraw) {
        $activeDraw = \App\Models\WeeklyDraw::where('status', 'active')->first();

        if (!$activeDraw) {
            Log::critical('⚠️ NO ACTIVE DRAW during business hours', [
                'day' => $now->format('l'),
                'time' => $now->format('H:i:s')
            ]);
            // Send alert to admin
            // Notification::send(User::role('admin')->get(), new NoActiveDrawAlert());
        }
    }

    // Check 2: Database connection
    try {
        DB::connection()->getPdo();
    } catch (\Exception $e) {
        Log::critical('⚠️ DATABASE CONNECTION FAILED', [
            'error' => $e->getMessage()
        ]);
        // Send critical alert
    }

    // Check 3: Pending donations older than 2 hours
    $stalePending = Donation::where('stripe_payment_status', 'pending')
        ->where('created_at', '<', now()->subHours(2))
        ->count();

    if ($stalePending > 10) {
        Log::warning("⚠️ {$stalePending} stale pending donations");
    }

    // Check 4: Redis connection (if using Redis)
    try {
        \Illuminate\Support\Facades\Cache::store('redis')->get('health_check');
    } catch (\Exception $e) {
        Log::error('⚠️ REDIS CONNECTION FAILED', [
            'error' => $e->getMessage()
        ]);
    }
})
    ->everyFiveMinutes()
    ->timezone(config('app.timezone'))
    ->name('system-health-check');

/**
 * ==================================================
 * BACKUP & MAINTENANCE (OPTIONAL)
 * ==================================================
 */

// Database backup - Daily at 4:00 AM
// Make sure to install: composer require spatie/laravel-backup
Schedule::command('backup:run --only-db')
    ->dailyAt('04:00')
    ->timezone(config('app.timezone'))
    ->name('database-backup')
    ->onFailure(function () {
        Log::critical('❌ Database backup failed!');
        // Send critical alert to admin
    });

/**
 * ==================================================
 * ARTISAN COMMANDS (Laravel 11 Style)
 * ==================================================
 */

// Custom console commands can be registered here
Artisan::command('draw:status', function () {
    $draw = \App\Models\WeeklyDraw::where('status', 'active')->first();

    if ($draw) {
        $this->info("Current Draw: Week #{$draw->week_number}");
        $this->info("Participants: {$draw->total_participants}");
        $this->info("Total Pool: $" . number_format($draw->total_pool, 2));
        $this->info("Status: {$draw->status}");
    } else {
        $this->warn('No active draw found');
    }
})->purpose('Check current draw status');

Artisan::command('draw:exclusions', function () {
    $exclusions = WinnerExclusion::where('is_active', true)
        ->where('exclusion_ends_at', '>', now())
        ->count();

    $this->info("Currently excluded users: {$exclusions}");

    $expiringSoon = WinnerExclusion::where('is_active', true)
        ->where('exclusion_ends_at', '>', now())
        ->where('exclusion_ends_at', '<', now()->addDays(30))
        ->count();

    $this->info("Expiring in next 30 days: {$expiringSoon}");
})->purpose('Check winner exclusions');

Artisan::command('draw:cleanup {--force}', function () {
    $force = $this->option('force');

    if (!$force) {
        if (!$this->confirm('This will clean up all expired data. Continue?')) {
            return;
        }
    }

    // Cleanup expired exclusions
    $service = app(WeeklyDrawService::class);
    $cleaned = $service->cleanupExpiredExclusions();
    $this->info("✅ Cleaned {$cleaned} expired exclusions");

    // Cleanup pending donations
    $deleted = Donation::where('stripe_payment_status', 'pending')
        ->where('created_at', '<', now()->subHours(24))
        ->delete();
    $this->info("✅ Cleaned {$deleted} pending donations");

    // Cleanup expired OTPs
    $expired = OtpLog::where('status', 'sent')
        ->where('expires_at', '<', now())
        ->update(['status' => 'expired']);
    $this->info("✅ Expired {$expired} OTP codes");

    $this->info('🎉 Cleanup completed!');
})->purpose('Manual cleanup of expired data');

Artisan::command('cache:clear-draw', function () {
    \Illuminate\Support\Facades\Cache::forget('excluded_user_ids');
    $this->info('✅ Draw cache cleared');
})->purpose('Clear draw-related cache');
