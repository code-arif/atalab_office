<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WorkController;
use App\Http\Controllers\Api\CMS\CmsController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\UserListController;
use App\Http\Controllers\Api\Draw\DrawController;
use App\Http\Controllers\Api\WorkScheduleRequest;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\Winner\WinningController;
use Laravel\Cashier\Http\Controllers\WebhookController;
use App\Http\Controllers\Api\Donation\DonationController;
use App\Http\Controllers\Api\Auth\AuthenticationController;
use App\Http\Controllers\Api\TestimonialController;

//health-check
Route::get("/check", function () {
    return "All Right 👍";
});

//Guest user routes
Route::group(['middleware' => 'guest:api'], function () {

    // Login & Register
    Route::post('/login', [AuthenticationController::class, 'login']);

    // cms home page data
    Route::get('/cms/home', [CmsController::class, 'home']);

    // cms our story page data
    Route::get('/cms/our-story', [CmsController::class, 'ourStory']);

    // how it works page data
    Route::get('/cms/how-it-works', [CmsController::class, 'howItWorks']);

    // structure page data
    Route::get('/cms/structure', [CmsController::class, 'structure']);

    // eligibility page data
    Route::get('/cms/eligibility', [CmsController::class, 'eligibility']);

    // payment policy page data
    Route::get('/cms/payment-policy', [CmsController::class, 'paymentPolicy']);

    // tax policy page data
    Route::get('/cms/tax-policy', [CmsController::class, 'taxPolicy']);

    // ethical-boundaries page data
    Route::get('/cms/ethical-boundaries', [CmsController::class, 'ethicalBoundaries']);

    // officer_compensation_policy page data
    Route::get('/cms/officer-compensation-policy', [CmsController::class, 'officerCompensationPolicy']);

    // archives page data
    Route::get('/cms/archives', [CmsController::class, 'archives']);

    // contact-us page data
    Route::get('/cms/contact-us', [CmsController::class, 'contactUs']);

    // partials - footer data
    Route::get('/cms/partials/footer', [CmsController::class, 'topbarPartials']);

    // subscribe newsletter
    Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe']);
});


Route::group(['middleware' => 'auth:api'], function () {
    //User logout
    Route::post('/logout', [AuthenticationController::class, 'logout']);

    // Get tesimonials
    Route::get('/testimonials', [TestimonialController::class, 'index']);

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

Route::group(['middleware' => 'guest:api'], function () {
    // Donation Routes
    Route::prefix('donations')->group(function () {
        Route::post('/quick', [DonationController::class, 'quickDonation']);
        Route::post('/custom', [DonationController::class, 'customDonation']);
        Route::get('/my-donations', [DonationController::class, 'myDonations']);
        Route::get('/{id}', [DonationController::class, 'show']);
    });

    // Winning Routes
    Route::prefix('winnings')->group(function () {
        Route::get('/my-winnings', [WinningController::class, 'myWinnings']);
        Route::post('/{id}/claim', [WinningController::class, 'claim']);
        Route::get('/check-status', [WinningController::class, 'checkWinningStatus']);
    });
});

/*
|--------------------------------------------------------------------------
| Webhook Routes (No Auth)
|--------------------------------------------------------------------------
*/

Route::post('/webhooks/stripe', [WebhookController::class, 'handleStripe']);
