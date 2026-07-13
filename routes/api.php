<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CMS\CmsController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\Chat\ChatApiController;
use App\Http\Controllers\Api\DrawSettingsController;
use App\Http\Controllers\Web\Backend\VisitorController;
use App\Http\Controllers\Api\Auth\RegistrationController;
use App\Http\Controllers\Api\Donation\DonationController;
use App\Http\Controllers\Api\Auth\AuthenticationController;
use App\Http\Controllers\Api\Donation\WeeklyDrawController;
use App\Http\Controllers\Api\V2\Donation\DonationV2Controller;

/*
|--------------------------------------------------------------------------
| Health Check
|--------------------------------------------------------------------------
|
| A simple endpoint to verify that the API server is running and
| responding correctly. Useful for uptime monitoring and load balancers.
|
*/

Route::get('/check', function () {
    return 'All Right 👍';
});

/*
|--------------------------------------------------------------------------
| Guest Routes (Unauthenticated)
|--------------------------------------------------------------------------
|
| These routes are accessible to unauthenticated (guest) users only.
| Authenticated users will be redirected away from these endpoints.
|
*/
Route::group(['middleware' => 'guest:api'], function () {

    // Authenticate an existing user and issue an API token.
    Route::post('/login', [AuthenticationController::class, 'login']);

    /*
    |--------------------------------------------------------------------------
    | CMS (Content Management System) Routes
    |--------------------------------------------------------------------------
    |
    | Publicly accessible endpoints for retrieving static page content
    | managed through the CMS (e.g., marketing and policy pages).
    |
    */
    Route::prefix('cms')->group(function () {
        Route::get('/home', [CmsController::class, 'home']); // Retrieve homepage content blocks.
        Route::get('/our-story', [CmsController::class, 'ourStory']); // Retrieve the "Our Story" page content.
        Route::get('/how-it-works', [CmsController::class, 'howItWorks']); // Retrieve the "How It Works" page content.
        Route::get('/privacy-policy', [CmsController::class, 'privacyPolicy']); // Retrieve the privacy policy page content.
        Route::get('/eligibility', [CmsController::class, 'eligibility']); // Retrieve the eligibility criteria page content.
        Route::get('/payment-policy', [CmsController::class, 'paymentPolicy']); // Retrieve the payment policy page content.
        Route::get('/tax-policy', [CmsController::class, 'taxPolicy']); // Retrieve the tax policy page content.
        Route::get('/terms-and-conditions', [CmsController::class, 'termsAndConditions']); // Retrieve the terms and conditions page content.
        Route::get('/officer-compensation-policy', [CmsController::class, 'officerCompensationPolicy']); // Retrieve the officer compensation policy page content.
        Route::get('/archives', [CmsController::class, 'archives']); // Retrieve archived content listings.
        Route::get('/contact-us', [CmsController::class, 'contactUs']); // Retrieve the "Contact Us" page content and metadata.
    });

    // Subscribe a new email address to the newsletter mailing list.
    Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe']);

    // Submit a new contact form message from a guest user.
    Route::post('/contact-us', [ContactController::class, 'store']);

    // Retrieve the current draw settings and configuration.
    Route::get('/draw-settings', [DrawSettingsController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Token Required)
|--------------------------------------------------------------------------
|
| These routes require a valid API token (Passport/Sanctum) issued upon
| login. All requests must include an Authorization: Bearer <token> header.
|
*/
Route::group(['middleware' => 'auth:api'], function () {

    // Revoke the current user's API token and log them out.
    Route::post('/logout', [AuthenticationController::class, 'logout']);
});

/*
|--------------------------------------------------------------------------
| Versioned API Routes (v1)
|--------------------------------------------------------------------------
|
| All routes within this group are prefixed with /api/v1, following
| REST versioning best practices. This allows non-breaking
| changes to be introduced in future API versions independently.
|
*/
Route::prefix('v1')->group(function () {

    /*
    |----------------------------------------------------------------------
    | Registration & Authentication
    |----------------------------------------------------------------------
    */

    // Register a new user account and initiate the OTP verification flow.
    Route::post('auth/register', [RegistrationController::class, 'register']);

    /*
    |----------------------------------------------------------------------
    | Weekly Draw — Public Information
    |----------------------------------------------------------------------
    |
    | These endpoints expose read-only weekly draw data and do not require
    | user authentication.
    |
    */
    Route::prefix('weekly-draw')->group(function () {

        // Retrieve information about the currently active weekly draw.
        Route::get('/current', [WeeklyDrawController::class, 'getCurrentDraw']);

        // Donation price
        Route::get('/price', [WeeklyDrawController::class, 'getPrice']);

        // Retrieve the list of winners for the most recent weekly draw.
        Route::get('/winners', [WeeklyDrawController::class, 'getWinners']);
    });

    /*
    |----------------------------------------------------------------------
    | Donation Routes
    |----------------------------------------------------------------------
    |
    | Endpoints for initiating and managing donations. A valid session
    | token may be required depending on the payment gateway's flow.
    |
    */
    Route::prefix('donate')->group(function () {

        // Initiate a standard (predefined amount) donation payment session.
        Route::post('/standard', [DonationController::class, 'createStandardDonation']);

        // Initiate a custom (user-defined amount) donation payment session.
        Route::post('/custom', [DonationController::class, 'createCustomDonation']);

        // Verify and confirm a payment after gateway callback/redirect.
        Route::post('/verify', [DonationController::class, 'verifyPayment']);

        // Check the current status of a donation payment by its payment ID.
        Route::get('/{paymentId}/status', [DonationController::class, 'checkPaymentStatus']);
    });
});

/*
|--------------------------------------------------------------------------
| Guest Chat Routes (No Authentication Required)
|--------------------------------------------------------------------------
|
| These endpoints allow unauthenticated (guest) users to participate in
| live chat sessions. Guest sessions are identified by a temporary token
| issued upon registration.
|
*/
Route::prefix('chat/guest')->name('chat.guest.')->group(function () {

    // Register a new guest chat session and receive a temporary session token.
    Route::post('/register',     [ChatApiController::class, 'guestRegister'])->name('register');

    // Send a message within an active guest chat session.
    Route::post('/send',         [ChatApiController::class, 'guestSendMessage'])->name('send');

    // Retrieve the full message history for a guest chat session.
    Route::get('/conversation',  [ChatApiController::class, 'guestGetConversation'])->name('conversation');

    // Broadcast a typing indicator event within a guest chat session.
    Route::post('/typing',       [ChatApiController::class, 'guestTyping'])->name('typing');
});

/*
|--------------------------------------------------------------------------
| Visitor Tracking Routes
|--------------------------------------------------------------------------
|
| Endpoints for recording and retrieving website visitor analytics.
| The tracking endpoint is publicly accessible and rate-limited to
| prevent abuse. The stats endpoint is intended for admin dashboards.
|
*/

// Record a new visitor event. Rate-limited to 60 requests per minute.
Route::post('/track-visitor', [VisitorController::class, 'trackVisitor'])
    ->middleware(['throttle:60,1']);

// Retrieve aggregated visitor statistics for the admin dashboard.
Route::prefix('dashboard')->group(function () {
    Route::get('/visitor-stats', [VisitorController::class, 'getVisitorStats']);
});

/*
|--------------------------------------------------------------------------
| V2 Payment Module Routes
|--------------------------------------------------------------------------
|
| V2 payment endpoints with card fingerprint tracking, processing fee
| management, and duplicate card detection. Entirely independent from
| the V1 payment flow — existing production code is untouched.
|
*/
Route::prefix('v2')->group(function () {

    /*
    |----------------------------------------------------------------------
    | V2 Donation Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('donate')->group(function () {

        // Initiate a standard donation with fee calculation (server-side).
        Route::post('/standard', [DonationV2Controller::class, 'createStandardDonation']); // done

        // Initiate a custom amount donation with fee calculation.
        Route::post('/custom', [DonationV2Controller::class, 'createCustomDonation']);

        // Verify payment after redirect from Stripe (with fingerprint extraction).
        Route::post('/verify', [DonationV2Controller::class, 'verifyPayment']); // done

        // Check payment status by Stripe payment ID.
        Route::get('/{paymentId}/status', [DonationV2Controller::class, 'checkPaymentStatus']);

        // Check if a card fingerprint has been used in the current draw.
        Route::post('/check-duplicate-card', [DonationV2Controller::class, 'checkDuplicateCard']);
    });
});
