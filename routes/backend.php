<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\CMS\Home\HeroController;
use App\Http\Controllers\Web\Backend\CMS\Home\QuoteController;
use App\Http\Controllers\Web\Backend\CMS\EthicalPageController;
use App\Http\Controllers\Web\Backend\CMS\PaymentPageController;
use App\Http\Controllers\Web\Backend\CMS\EligibilityPageController;
use App\Http\Controllers\Web\Backend\CMS\Home\GalleryController;
use App\Http\Controllers\Web\Backend\Settings\ProfileController;
use App\Http\Controllers\Web\Backend\Settings\SettingController;
use App\Http\Controllers\Web\Backend\CMS\Home\OurStoryController;
use App\Http\Controllers\Web\Backend\CMS\TaxPolicyPageController;
use App\Http\Controllers\Web\Backend\CMS\Home\WeBelieveController;
use App\Http\Controllers\Web\Backend\CMS\Home\DisclaimerController;
use App\Http\Controllers\Web\Backend\CMS\Home\PercentageController;
use App\Http\Controllers\Web\Backend\CMS\Home\TestimonialController;
use App\Http\Controllers\Web\Backend\CMS\Home\DistributionController;
use App\Http\Controllers\Web\Backend\CMS\Home\NameSelectedController;
use App\Http\Controllers\Web\Backend\CMS\Home\FounderStatementController;
use App\Http\Controllers\Web\Backend\CMS\OurStory\OurStoryPageController;
use App\Http\Controllers\Web\Backend\CMS\HowItWorks\StructurePageController;
use App\Http\Controllers\Web\Backend\CMS\HowItWorks\HowItWorksPageController;

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

        // home page testimonial section
        Route::get('/home/testimonial', [TestimonialController::class, 'index'])->name('home.testimonial.section');
        Route::post('/home/testimonial/update', [TestimonialController::class, 'update'])->name('home.testimonial.section.update');

        // home page gallery section
        Route::get('/home/gallery', [GalleryController::class, 'index'])->name('home.gallery.section');
        Route::post('/home/gallery/update', [GalleryController::class, 'update'])->name('home.gallery.section.update');

        // home page disclaimer section
        Route::get('/home/disclaimer', [DisclaimerController::class, 'index'])->name('home.disclaimer.section');
        Route::post('/home/disclaimer/update', [DisclaimerController::class, 'update'])->name('home.disclaimer.section.update');

        // home page we believe section
        Route::get('/home/we-believe', [WeBelieveController::class, 'index'])->name('home.we_believe.section');
        Route::post('/home/we-believe/update', [WeBelieveController::class, 'update'])->name('home.we_believe.section.update');

        // home page founder statement section
        Route::get('/home/founder-statement', [FounderStatementController::class, 'index'])->name('home.founder_statement.section');
        Route::post('/home/founder-statement/update', [FounderStatementController::class, 'update'])->name('home.founder_statement.section.update');

        // our story page hero section
        Route::get('/our-story/hero', [OurStoryPageController::class, 'index'])->name('our_story.hero.section');
        Route::post('/our-story/hero/update', [OurStoryPageController::class, 'update'])->name('our_story.hero.section.update');

        // how it works page hero section
        Route::get('/how-it-works/hero', [HowItWorksPageController::class, 'index'])->name('how_it_works.hero.section');
        Route::post('/how-it-works/hero/update', [HowItWorksPageController::class, 'update'])->name('how_it_works.hero.section.update');

        // structure page hero section
        Route::get('/structure/hero', [StructurePageController::class, 'index'])->name('structure.hero.section');
        Route::post('/structure/hero/update', [StructurePageController::class, 'update'])->name('structure.hero.section.update');

        // eligibility page hero section
        Route::get('/eligibility/hero', [EligibilityPageController::class, 'index'])->name('eligibility.hero.section');
        Route::post('/eligibility/hero/update', [EligibilityPageController::class, 'update'])->name('eligibility.hero.section.update');

        // payment policy page hero section
        Route::get('/payment-policy/hero', [PaymentPageController::class, 'index'])->name('payment_policy.hero.section');
        Route::post('/payment-policy/hero/update', [PaymentPageController::class, 'update'])->name('payment_policy.hero.section.update');

        // tax policy page hero section
        Route::get('/tax-policy/hero', [TaxPolicyPageController::class, 'index'])->name('tax_policy.hero.section');
        Route::post('/tax-policy/hero/update', [TaxPolicyPageController::class, 'update'])->name('tax_policy.hero.section.update');

        // ethical page hero section
        Route::get('/ethical/hero', [EthicalPageController::class, 'index'])->name('ethical.hero.section');
        Route::post('/ethical/hero/update', [EthicalPageController::class, 'update'])->name('ethical.hero.section.update');
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
