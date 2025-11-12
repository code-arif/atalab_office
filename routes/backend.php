<?php

use Illuminate\Support\Facades\Route;
use Google\Service\Analytics\Resource\Management;
use App\Http\Controllers\Web\Backend\ReviewController;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\SubscriberController;
use App\Http\Controllers\Web\Backend\CMS\Home\HeroController;
use App\Http\Controllers\Web\Backend\CMS\Home\QuoteController;
use App\Http\Controllers\Web\Backend\CMS\ArchivePageController;
use App\Http\Controllers\Web\Backend\CMS\EthicalPageController;
use App\Http\Controllers\Web\Backend\CMS\Home\SliderController;
use App\Http\Controllers\Web\Backend\CMS\PaymentPageController;
use App\Http\Controllers\Web\Backend\CMS\FooterManageController;
use App\Http\Controllers\Web\Backend\CMS\Home\GalleryController;
use App\Http\Controllers\Web\Backend\CMS\TopBarManageController;
use App\Http\Controllers\Web\Backend\Settings\ProfileController;
use App\Http\Controllers\Web\Backend\Settings\SettingController;
use App\Http\Controllers\Web\Backend\CMS\ContactUsPageController;
use App\Http\Controllers\Web\Backend\CMS\Home\OurStoryController;
use App\Http\Controllers\Web\Backend\CMS\TaxPolicyPageController;
use App\Http\Controllers\Web\Backend\Donation\DonationController;
use App\Http\Controllers\Web\Backend\CMS\Home\WeBelieveController;
use App\Http\Controllers\Web\Backend\CMS\EligibilityPageController;
use App\Http\Controllers\Web\Backend\CMS\Home\DisclaimerController;
use App\Http\Controllers\Web\Backend\CMS\Home\PercentageController;
use App\Http\Controllers\Web\Backend\Donation\DrawWinnerController;
use App\Http\Controllers\Web\Backend\Donation\WeeklyDrawController;
use App\Http\Controllers\Web\Backend\CMS\Home\TestimonialController;
use App\Http\Controllers\Web\Backend\CMS\OfficersCompPageController;
use App\Http\Controllers\Web\Backend\CMS\Home\DistributionController;
use App\Http\Controllers\Web\Backend\CMS\Home\NameSelectedController;
use App\Http\Controllers\Web\Backend\CMS\Home\FounderStatementController;
use App\Http\Controllers\Web\Backend\CMS\OurStory\OurStoryPageController;
use App\Http\Controllers\Web\Backend\CMS\HowItWorks\StructurePageController;
use App\Http\Controllers\Web\Backend\CMS\HowItWorks\HowItWorksPageController;

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // newsletter subscribers
    Route::get('/subscribers', [SubscriberController::class, 'index'])
        ->name('subscribers.index');

    // rating and reviews
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/store', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/edit/{id}', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::get('/reviews/show/{id}', [ReviewController::class, 'show'])->name('reviews.show'); // NEW
    Route::post('/reviews/update/{id}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/delete/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy');


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
        Route::get('/ethical/hero', [EthicalPageController::class, 'index'])->name('ethical_boundaries.hero.section');
        Route::post('/ethical/hero/update', [EthicalPageController::class, 'update'])->name('ethical_boundaries.hero.section.update');

        // officer compensation page hero section
        Route::get('/officer-compensation/hero', [OfficersCompPageController::class, 'index'])->name('officer_compensation.hero.section');
        Route::post('/officer-compensation/hero/update', [OfficersCompPageController::class, 'update'])->name('officer_compensation.hero.section.update');

        // archive page hero section
        Route::get('/archive/hero', [ArchivePageController::class, 'index'])->name('archive.hero.section');
        Route::post('/archive/hero/update', [ArchivePageController::class, 'update'])->name('archive.hero.section.update');

        // contact us page hero section
        Route::get('/contact-us/hero', [ContactUsPageController::class, 'index'])->name('contact_us.hero.section');
        Route::post('/contact-us/hero/update', [ContactUsPageController::class, 'update'])->name('contact_us.hero.section.update');

        // topbar section
        Route::get('/topbar', [TopBarManageController::class, 'index'])->name('topbar.section');
        Route::post('/topbar/update', [TopBarManageController::class, 'update'])->name('topbar.section.update');

        // footer section
        Route::get('/footer', [FooterManageController::class, 'index'])->name('footer.section');
        Route::post('/footer/update', [FooterManageController::class, 'update'])->name('footer.section.update');

        // Slider Management Routes
        Route::get('/slider', [SliderController::class, 'index'])->name('slider.index');
        Route::post('/slider/store', [SliderController::class, 'store'])->name('slider.store');
        Route::post('/slider/{id}/status', [SliderController::class, 'updateStatus'])->name('slider.status');
        Route::delete('/slider/{id}', [SliderController::class, 'destroy'])->name('slider.destroy');
        Route::post('/slider/update-order', [SliderController::class, 'updateOrder'])->name('slider.updateOrder');
    });


    // Weekly Draw Management
    // Route::get('/weekly-draw/all', [WeeklyDrawController::class, 'getAllDraws'])->name('get.all.weekly.draw');
    // // Route::post('/weekly-draw/create', [WeeklyDrawController::class, 'createNewDraw'])->name('');
    // // Route::post('/weekly-draw/{weekId}/finalize', [WeeklyDrawController::class, 'finalizeDraw']);
    // // Route::post('/weekly-draw/{weekId}/select-winners', [WeeklyDrawController::class, 'selectWinners']);

    // // Donation Management
    // Route::get('/donations', [DonationController::class, 'getAllDonations']);
    // Route::get('/donations/week/{weekId}', [DonationController::class, 'getDonationsByWeek']);

    // // Winner Management
    // Route::get('/winners', [DrawWinnerController::class, 'getAllWinners']);
    // Route::post('/winners/{winnerId}/process-payout', [DrawWinnerController::class, 'processPayout']);
    // Route::get('/winners/pending-payouts', [DrawWinnerController::class, 'getPendingPayouts']);

    // // Statistics
    // Route::get('/stats/overview', [WeeklyDrawController::class, 'getOverviewStats']);


    Route::prefix('weekly-draws')->name('weekly-draws.')->group(function () {
        Route::get('/', [WeeklyDrawController::class, 'index'])->name('index'); // working
        Route::post('/store', [WeeklyDrawController::class, 'store'])->name('store'); // working
        Route::get('/{id}', [WeeklyDrawController::class, 'show'])->name('show');
        Route::post('/{id}/finalize', [WeeklyDrawController::class, 'finalize'])->name('finalize');
        Route::post('/{id}/select-winners', [WeeklyDrawController::class, 'selectWinners'])->name('select-winners');
        Route::delete('/{id}', [WeeklyDrawController::class, 'destroy'])->name('destroy');
    });

    Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // View all draws (auto-generated)
    // Route::get('/draws', [WeeklyDrawController::class, 'index']);

    // // Manual override (emergency only)
    // Route::post('/draws/emergency-create', [WeeklyDrawController::class, 'store']);
    // Route::post('/draws/{id}/emergency-finalize', [WeeklyDrawController::class, 'finalize']);

    // View winners
    Route::get('/winners', [DrawWinnerController::class, 'index']);
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
