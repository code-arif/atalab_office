<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WorkController;
use App\Http\Controllers\Api\CMS\CmsController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\UserListController;
use App\Http\Controllers\Api\WorkScheduleRequest;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\Auth\AuthenticationController;

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
    Route::get('/cms/structure', [CmsController::class,'structure']);

    // eligibility page data
    Route::get('/cms/eligibility', [CmsController::class,'eligibility']);

    // payment policy page data
    Route::get('/cms/payment-policy', [CmsController::class,'paymentPolicy']);

    // tax policy page data
    Route::get('/cms/tax-policy', [CmsController::class,'taxPolicy']);

    // ethical-boundaries page data
    Route::get('/cms/ethical-boundaries', [CmsController::class,'ethicalBoundaries']);

    // officer_compensation_policy page data
    Route::get('/cms/officer-compensation-policy', [CmsController::class,'officerCompensationPolicy']);

    // archives page data
    Route::get('/cms/archives', [CmsController::class,'archives']);

    // contact-us page data
    Route::get('/cms/contact-us', [CmsController::class,'contactUs']);

    // partials - footer data
    Route::get('/cms/partials/footer', [CmsController::class,'topbarPartials']);

    // subscribe newsletter
    Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe']);
});


Route::group(['middleware' => 'auth:api'], function () {
    //User logout
    Route::post('/logout', [AuthenticationController::class, 'logout']);

    //employee list
    Route::get('/employee-list', [UserListController::class, 'index']);

    // Work reschedule request
    Route::post('/reschedule-request', [WorkScheduleRequest::class, 'upsert']); // working
    Route::get('/reschedule-request/edit/{id}', [WorkScheduleRequest::class, 'edit']); // working
    Route::delete('/reschedule-request/delete/{id}', [WorkScheduleRequest::class, 'destroy']); // working
    Route::post('/self-reschedule/{id}', [WorkScheduleRequest::class, 'selfReschedule']); // working


    // Work manage
    Route::group(['prefix' => 'work'], function () {
        Route::get('/list', [WorkController::class, 'index']);
        Route::get('/map', [WorkController::class, 'mapView']);
        Route::post('/complete/{id}', [WorkController::class, 'completeWork']); // work complation
        Route::post('/incomplete/{id}', [WorkController::class, 'inCompleteWork']); // work imcomplation
        Route::get('/details/{id}', [WorkController::class, 'show']);
    });


    Route::post('/location/update', [LocationController::class, 'update']);
    Route::get('/locations/current', [LocationController::class, 'getCurrentLocations']);
    Route::get('/team/{teamId}/history', [LocationController::class, 'getTeamHistory']);
});
