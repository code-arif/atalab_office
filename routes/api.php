<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Controllers\Api\CMS\CmsController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\TestimonialController;
use App\Http\Controllers\Api\Chat\ChatApiController;
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
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {

    // ============================================
    // REGISTRATION & OTP ROUTES
    // ============================================
    Route::prefix('auth')->group(function () {
        Route::post('/register', [RegistrationController::class, 'register']); // working
        Route::post('/resend-otp', [RegistrationController::class, 'resendOTP']); // working
        Route::post('/verify-otp', [RegistrationController::class, 'verifyOTP']); // working
    });

    // ============================================
    // WEEKLY DRAW INFORMATION (PUBLIC)
    // ============================================
    Route::prefix('weekly-draw')->group(function () {
        Route::get('/current', [WeeklyDrawController::class, 'getCurrentDraw']);
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
});



// ============================================
// GUEST CHAT ROUTES (NO AUTH REQUIRED)
// ============================================
Route::prefix('chat/guest')->name('chat.guest.')->group(function () {
    Route::post('/register', [ChatApiController::class, 'guestRegister'])->name('register');
    Route::post('/send', [ChatApiController::class, 'guestSendMessage'])->name('send');
    Route::get('/conversation', [ChatApiController::class, 'guestGetConversation'])->name('conversation');
    Route::post('/typing', [ChatApiController::class, 'guestTyping'])->name('typing');
});
