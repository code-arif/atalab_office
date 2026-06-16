<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Api\Donation\DonationController;
use App\Http\Controllers\Api\V2\Donation\DonationV2Controller;
use App\Http\Controllers\Api\Auth\AuthenticationController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/run-migrate', function () {
    try {
        $output = Artisan::call('migrate:fresh');
        return response()->json([
            'message' => 'Migrations executed.',
            'output' => nl2br($output)
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred while running migrations.',
            'error' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/run-migrate-fresh', function () {
    try {
        $output = Artisan::call('migrate:fresh', ['--seed' => true]);
        return response()->json([
            'message' => 'Migrations executed.',
            'output' => nl2br($output)
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred while running migrations.',
            'error' => $e->getMessage(),
        ], 500);
    }
});

// Run composer update
Route::get('/run-composer-update', function () {
    $output = shell_exec('composer update 2>&1');
    return response()->json([
        'message' => 'Composer update command executed.',
        'output' => nl2br($output)
    ]);
});
// Run optimize:clear
Route::get('/run-optimize-clear', function () {
    $output = Artisan::call('optimize:clear');
    return response()->json([
        'message' => 'Optimize clear command executed.',
        'output' => nl2br($output)
    ]);
});
// Run db:seed
Route::get('/run-db-seed', function () {
    $output = Artisan::call('db:seed', ['--force' => true]);
    return response()->json([
        'message' => 'Database seeding executed.',
        'output' => nl2br($output)
    ]);
});
// Run cache:clear
Route::get('/run-cache-clear', function () {
    $output = Artisan::call('cache:clear');
    return response()->json([
        'message' => 'Cache cleared.',
        'output' => nl2br($output)
    ]);
});
// Run queue:restart
Route::get('/run-queue-restart', function () {
    $output = Artisan::call('queue:restart');
    return response()->json([
        'message' => 'Queue workers restarted.',
        'output' => nl2br($output)
    ]);
});

// Create storage symbolic link
Route::get('/run-storage-link', function () {
    try {
        $output = Artisan::call('storage:link');
        return response()->json([
            'message' => 'Storage symbolic link created.',
            'output' => nl2br($output)
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred while creating storage symbolic link.',
            'error' => $e->getMessage(),
        ], 500);
    }
});


// teacher email verification
Route::get('/verify-email/{token}', [AuthenticationController::class, 'verifyEmail'])->name('verify.email');



// ============================================
// STRIPE WEBHOOK (NO AUTH REQUIRED)
// ============================================
Route::post('/webhook/stripe', [DonationController::class, 'handleStripeWebhook']);

// V2 Stripe Webhook — Dedicated endpoint for V2 payment module.
// Configure this URL as a second webhook endpoint in Stripe Dashboard:
// Endpoint URL: https://your-domain.com/api/v2/webhook/stripe
Route::post('/v2/webhook/stripe', [DonationV2Controller::class, 'handleStripeWebhook']);


require __DIR__ . '/auth.php';
