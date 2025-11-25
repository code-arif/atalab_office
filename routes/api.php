<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Controllers\Api\CMS\CmsController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\TestimonialController;
use App\Http\Controllers\Api\DrawSettingsController;
use App\Http\Controllers\Api\Auth\RegistrationController;
use App\Http\Controllers\Api\Donation\DonationController;
use App\Http\Controllers\Api\Auth\AuthenticationController;
use App\Http\Controllers\Api\Donation\WeeklyDrawController;

//health-check
Route::get("/check", function () {
    return "All Right 👍";
});

//Guest user routes
Route::group(['middleware' => 'guest:api'], function () {

    // Login & Register
    Route::post('/login', [AuthenticationController::class, 'login']);

    // cms route gorup
    Route::group(['prefix' => 'cms'], function () {
        Route::get('/home', [CmsController::class, 'home']); // cms home page data
        Route::get('/slider', [CmsController::class, 'getSlider']); // Slider
        Route::get('/our-story', [CmsController::class, 'ourStory']); // cms our story page data
        Route::get('/how-it-works', [CmsController::class, 'howItWorks']); // how it works page data
        Route::get('/structure', [CmsController::class, 'structure']); // structure page data
        Route::get('/eligibility', [CmsController::class, 'eligibility']); // eligibility page data
        Route::get('/payment-policy', [CmsController::class, 'paymentPolicy']); // payment policy page data
        Route::get('/tax-policy', [CmsController::class, 'taxPolicy']); // tax policy page data
        Route::get('/ethical-boundaries', [CmsController::class, 'ethicalBoundaries']); // ethical-boundaries page data
        Route::get('/officer-compensation-policy', [CmsController::class, 'officerCompensationPolicy']); // officer compensation policy page data
        Route::get('/archives', [CmsController::class, 'archives']); // archives page data
        Route::get('/contact-us', [CmsController::class, 'contactUs']); // contact-us page data
        Route::get('/partials/topbar', [CmsController::class, 'topbarData']); // partials - topbar data

        // partials - footer data
        Route::get('/partials/footer', [CmsController::class, 'footerData']);

        // Get tesimonials
        Route::get('/testimonials', [TestimonialController::class, 'index']);
    });

    // subscribe newsletter
    Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe']); // working

    // countact us
    Route::post('/contact-us', [ContactController::class, 'store']); // working

    // get draw setting table
    Route::get('/draw-settings', [DrawSettingsController::class, 'index']); // working

});


Route::group(['middleware' => 'auth:api'], function () {
    //User logout
    Route::post('/logout', [AuthenticationController::class, 'logout']);
});


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Draw Information (Public)
// Route::prefix('draws')->group(function () {
//     Route::get('/current', [DrawController::class, 'current']);
//     Route::get('/history', [DrawController::class, 'history']);
//     Route::get('/{id}', [DrawController::class, 'show']);
//     Route::get('/{id}/leaderboard', [DrawController::class, 'leaderboard']);
// });

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

// Public Routes (No Authentication)
// Route::prefix('v1')->group(function () {

//     // Weekly Draw Information
//     Route::get('/weekly-draw/current', [WeeklyDrawController::class, 'getCurrentDraw']);
//     // Route::get('/weekly-draw/{weekNumber}', [WeeklyDrawController::class, 'getDrawByWeek']);
//     // Route::get('/weekly-draw/{weekId}/stats', [WeeklyDrawController::class, 'getDrawStats']);

//     // Donation Routes
//     Route::post('/donate/standard', [DonationController::class, 'createStandardDonation']); // $25 donation -- working
//     Route::post('/donate/custom', [DonationController::class, 'createCustomDonation']); // Custom amount -- working
//     Route::post('/donation/verify', [DonationController::class, 'verifyPayment']); // Stripe webhook callback -- working

//     // Check donation status
//     Route::get('/donation/{paymentId}/status', [DonationController::class, 'checkPaymentStatus']);

//     // // Winner Routes
//     // Route::get('/winners/week/{weekNumber}', [DrawWinnerController::class, 'getWeeklyWinners']);
//     // Route::get('/winners/latest', [WinningController::class, 'myWinnings']);
// });



Route::prefix('v1')->group(function () {

    // ============================================
    // REGISTRATION & OTP ROUTES
    // ============================================
    Route::prefix('auth')->group(function () {
        Route::post('/register', [RegistrationController::class, 'register']); // working
        Route::post('/verify-otp', [RegistrationController::class, 'verifyOTP']); // working
        Route::post('/resend-otp', [RegistrationController::class, 'resendOTP']); // working
        Route::post('/validate-session', [RegistrationController::class, 'validateSession']);
    });

    // ============================================
    // WEEKLY DRAW INFORMATION (PUBLIC)
    // ============================================
    Route::prefix('weekly-draw')->group(function () {
        Route::get('/current', [WeeklyDrawController::class, 'getCurrentDraw']);
        Route::get('/{weekNumber}', [WeeklyDrawController::class, 'getDrawByWeek']);
        Route::get('/{weekId}/stats', [WeeklyDrawController::class, 'getDrawStats']);
    });

    // ============================================
    // DONATION ROUTES (REQUIRES SESSION TOKEN)
    // ============================================
    Route::prefix('donate')->group(function () {
        Route::post('/standard', [DonationController::class, 'createStandardDonation']);
        Route::post('/custom', [DonationController::class, 'createCustomDonation']);
        Route::post('/verify', [DonationController::class, 'verifyPayment']);
        Route::get('/{paymentId}/status', [DonationController::class, 'checkPaymentStatus']);
    });

    // ============================================
    // STRIPE WEBHOOK (NO AUTH REQUIRED)
    // ============================================
    Route::post('/webhook/stripe', [DonationController::class, 'handleStripeWebhook']);

    // ============================================
    // ADMIN ROUTES (ADD AUTHENTICATION LATER)
    // ============================================
    Route::prefix('admin')->group(function () {
        // Weekly Draw Management
        Route::get('/draws', [WeeklyDrawController::class, 'getAllDraws']);
        Route::post('/draws/create', [WeeklyDrawController::class, 'createNewDraw']);
        Route::post('/draws/{weekId}/finalize', [WeeklyDrawController::class, 'finalizeDraw']);
        Route::post('/draws/{weekId}/select-winners', [WeeklyDrawController::class, 'selectWinners']);
        Route::get('/draws/stats', [WeeklyDrawController::class, 'getOverviewStats']);

        // Donation Management
        Route::get('/donations', [DonationController::class, 'getAllDonations']);
        Route::get('/donations/week/{weekId}', [DonationController::class, 'getDonationsByWeek']);
    });
});




/*
|--------------------------------------------------------------------------
| Webhook Routes (No Auth)
|--------------------------------------------------------------------------
*/

Route::post('/webhook/stripe', [DonationController::class, 'handleStripeWebhook']);
