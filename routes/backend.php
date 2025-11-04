<?php

use App\Http\Controllers\Web\Backend\CMS\Home\DistributionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Backend\CMS\Home\HeroController;
use App\Http\Controllers\Web\Backend\CMS\Home\NameSelectedController;
use App\Http\Controllers\Web\Backend\CMS\Home\OurStoryController;
use App\Http\Controllers\Web\Backend\CMS\Home\PercentageController;
use App\Http\Controllers\Web\Backend\CMS\Home\QuoteController;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\Settings\ProfileController;
use App\Http\Controllers\Web\Backend\Settings\SettingController;

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // cms management
    Route::prefix('cms')->name('cms.')->group(function () {
        // home - hero section
        Route::get('/home/hero', [HeroController::class, 'index'])->name('home.hero.section');
        Route::post('/home/hero/update', [HeroController::class, 'update'])->name('home.hero.section.update');

        // home distribution section
        Route::get('/home/disctibution', [DistributionController::class, 'index'])->name('home.distribution.section');
        Route::post('/home/disctibution/update', [DistributionController::class, 'update'])->name('home.distribution.section.update');

        // home page percentage section
        Route::get('/home/percentage', [PercentageController::class, 'index'])->name('home.percentage.section');
        Route::post('/home/percentage/update', [PercentageController::class, 'update'])->name('home.percentage.section.update');

        // home page selected name
        Route::get('/home/selected-name', [NameSelectedController::class, 'index'])->name('home.selected_name.section');
        Route::post('/home/selected-name/update', [NameSelectedController::class, 'update'])->name('home.selected_name.section.update');

        // home page qutoe name
        Route::get('/home/quote', [QuoteController::class, 'index'])->name('home.quote.section');
        Route::post('/home/quote/update', [QuoteController::class, 'update'])->name('home.quote.section.update');

        // home page our story section
        Route::get('/home/our-story', [OurStoryController::class, 'index'])->name('home.our_story.section');
        Route::post('/home/our-story/update', [OurStoryController::class, 'update'])->name('home.our_story.section.update');
    });
});



//! Route for Profile Settings
Route::controller(ProfileController::class)->group(function () {
    Route::get('setting/profile', 'index')->name('setting.profile.index');
    Route::put('setting/profile/update', 'UpdateProfile')->name('setting.profile.update');
    Route::put('setting/profile/update/Password', 'UpdatePassword')->name('setting.profile.update.Password');
    Route::post('setting/profile/update/Picture', 'UpdateProfilePicture')->name('update.profile.picture');
});



//! Route for Stripe Settings
Route::controller(SettingController::class)->group(function () {
    Route::get('setting/general', 'index')->name('setting.general.index');
    Route::patch('setting/general', 'update')->name('setting.general.update');
});
