<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CMS\CmsController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\Draw\DrawController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\TestimonialController;
use App\Http\Controllers\Api\DrawSettingsController;
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

    // cmss route gorup
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
Route::prefix('draws')->group(function () {
    Route::get('/current', [DrawController::class, 'current']);
    Route::get('/history', [DrawController::class, 'history']);
    Route::get('/{id}', [DrawController::class, 'show']);
    Route::get('/{id}/leaderboard', [DrawController::class, 'leaderboard']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

// Public Routes (No Authentication)
Route::prefix('v1')->group(function () {

    // Weekly Draw Information
    Route::get('/weekly-draw/current', [WeeklyDrawController::class, 'getCurrentDraw']);
    // Route::get('/weekly-draw/{weekNumber}', [WeeklyDrawController::class, 'getDrawByWeek']);
    // Route::get('/weekly-draw/{weekId}/stats', [WeeklyDrawController::class, 'getDrawStats']);

    // Donation Routes
    Route::post('/donate/standard', [DonationController::class, 'createStandardDonation']); // $25 donation -- working
    Route::post('/donate/custom', [DonationController::class, 'createCustomDonation']); // Custom amount -- working
    Route::post('/donation/verify', [DonationController::class, 'verifyPayment']); // Stripe webhook callback -- working

    // Check donation status
    Route::get('/donation/{paymentId}/status', [DonationController::class, 'checkPaymentStatus']);

    // // Winner Routes
    // Route::get('/winners/week/{weekNumber}', [DrawWinnerController::class, 'getWeeklyWinners']);
    // Route::get('/winners/latest', [DrawWinnerController::class, 'getLatestWinners']);
});

// Route::post('/checkout', [DonationController::class,'checkout']);
Route::controller(DonationController::class)->prefix('payment/stripe')->name('payment.stripe.')->group(function () {
    Route::post('/checkout', 'checkout');
    Route::get('/success', 'success')->name('success');
    Route::get('/cancel', 'failure')->name('cancel');
});

/*
|--------------------------------------------------------------------------
| Webhook Routes (No Auth)
|--------------------------------------------------------------------------
*/

Route::post('/webhook/stripe', [DonationController::class, 'handleStripeWebhook']);
