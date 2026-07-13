<?php

use App\Http\Controllers\Web\Backend\Chat\ChatWebController;
use App\Http\Controllers\Web\Backend\CMS\ArchivePageController;
use App\Http\Controllers\Web\Backend\CMS\ContactUsPageController;
use App\Http\Controllers\Web\Backend\CMS\DrawSettingController;
use App\Http\Controllers\Web\Backend\CMS\EligibilityPageController;
use App\Http\Controllers\Web\Backend\CMS\PrivacyPolicyPageController;
use App\Http\Controllers\Web\Backend\CMS\FooterManageController;
use App\Http\Controllers\Web\Backend\CMS\Home\DisclaimerController;
use App\Http\Controllers\Web\Backend\CMS\Home\DistributionController;
use App\Http\Controllers\Web\Backend\CMS\Home\FounderStatementController;
use App\Http\Controllers\Web\Backend\CMS\Home\GalleryController;
use App\Http\Controllers\Web\Backend\CMS\Home\HeroController;
use App\Http\Controllers\Web\Backend\CMS\Home\NameSelectedController;
use App\Http\Controllers\Web\Backend\CMS\Home\OurStoryController;
use App\Http\Controllers\Web\Backend\CMS\Home\PercentageController;
use App\Http\Controllers\Web\Backend\CMS\Home\QuoteController;
use App\Http\Controllers\Web\Backend\CMS\Home\SliderController;
use App\Http\Controllers\Web\Backend\CMS\Home\TestimonialController;
use App\Http\Controllers\Web\Backend\CMS\Home\VideoController;
use App\Http\Controllers\Web\Backend\CMS\Home\WeBelieveController;
use App\Http\Controllers\Web\Backend\CMS\HowItWorks\HowItWorksPageController;
use App\Http\Controllers\Web\Backend\CMS\HowItWorks\TermsConditionsPageController;
use App\Http\Controllers\Web\Backend\CMS\OfficersCompPageController;
use App\Http\Controllers\Web\Backend\CMS\OurStory\OurStoryPageController;
use App\Http\Controllers\Web\Backend\CMS\PaymentPageController;
use App\Http\Controllers\Web\Backend\CMS\TaxPolicyPageController;
use App\Http\Controllers\Web\Backend\CMS\TopBarManageController;
use App\Http\Controllers\Web\Backend\ContactUsController;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\Donation\DonationController;
use App\Http\Controllers\Web\Backend\Donation\DrawWinnerController;
use App\Http\Controllers\Web\Backend\Donation\WeeklyDrawController;
use App\Http\Controllers\Web\Backend\Donation\ManualFinalizeController;
use App\Http\Controllers\Web\Backend\Donation\WinnerVerificationController;
use App\Http\Controllers\Web\Backend\ReviewController;
use App\Http\Controllers\Web\Backend\Settings\ProfileController;
use App\Http\Controllers\Web\Backend\Settings\SettingController;
use App\Http\Controllers\Web\Backend\Settings\StripeSettingsController;
use App\Http\Controllers\Web\Backend\Settings\DrawAutomateSettingController;
use App\Http\Controllers\Web\Backend\SubscriberController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->group(function () {

    // ------------------------------------------------------------------
    // Dashboard
    // Provides the main dashboard view and real-time data endpoints
    // ------------------------------------------------------------------
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/live-stats', [DashboardController::class, 'liveStatsApi']);
        Route::get('/recent-donations', [DashboardController::class, 'recentDonationsApi']);
    });

    // ------------------------------------------------------------------
    // Newsletter Subscribers
    // Displays the list of all newsletter subscribers.
    // ------------------------------------------------------------------
    Route::get('/subscribers', [SubscriberController::class, 'index'])
        ->name('subscribers.index');

    // ------------------------------------------------------------------
    // Reviews & Ratings
    // Manages user-submitted ratings and reviews.
    // ------------------------------------------------------------------
    Route::post('/reviews/store', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/edit/{id}', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::get('/reviews/show/{id}', [ReviewController::class, 'show'])->name('reviews.show');
    Route::post('/reviews/update/{id}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/delete/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy');


    // ------------------------------------------------------------------
    // CMS Management
    // Controls all Content Management System (CMS) sections, including
    // homepage sections, static pages, topbar, and footer.
    // ------------------------------------------------------------------
    Route::prefix('cms')->name('cms.')->group(function () {

        // Home Page — Hero Section
        Route::get('/home/hero', [HeroController::class, 'index'])->name('home.hero.section');
        Route::post('/home/hero/update', [HeroController::class, 'update'])->name('home.hero.section.update');

        // Home Page — Slider Management
        Route::get('/slider', [SliderController::class, 'index'])->name('slider.index');
        Route::post('/slider/store', [SliderController::class, 'store'])->name('slider.store');
        Route::post('/slider/{id}/status', [SliderController::class, 'updateStatus'])->name('slider.status');
        Route::delete('/slider/{id}', [SliderController::class, 'destroy'])->name('slider.destroy');
        Route::post('/slider/update-order', [SliderController::class, 'updateOrder'])->name('slider.updateOrder');

        // Home Page — Distribution Section
        Route::get('/home/disctibution', [DistributionController::class, 'index'])->name('home.distribution.section');
        Route::post('/home/disctibution/update', [DistributionController::class, 'update'])->name('home.distribution.section.update');
        Route::post('/home/disctibution/item/store', [DistributionController::class, 'itemStore'])->name('home.distribution.item.store');
        Route::post('/home/disctibution/item/update/{id}', [DistributionController::class, 'itemUpdate'])->name('home.distribution.item.update');
        Route::delete('/home/disctibution/item/destroy/{id}', [DistributionController::class, 'itemDelete'])->name('home.distribution.item.delete');

        // Home Page — Sample Video Section
        Route::get('/home/video', [VideoController::class, 'index'])->name('home.video.section');
        Route::post('/home/video/update', [VideoController::class, 'update'])->name('home.video.section.update');

        // Home Page — Percentage Section
        Route::get('/home/percentage', [PercentageController::class, 'index'])->name('home.percentage.section');
        Route::post('/home/percentage/update', [PercentageController::class, 'update'])->name('home.percentage.section.update');

        // Home Page — Selected Name Section
        Route::get('/home/selected-name', [NameSelectedController::class, 'index'])->name('home.selected_name.section');
        Route::post('/home/selected-name/update', [NameSelectedController::class, 'update'])->name('home.selected_name.section.update');

        // Home Page — Quote Section
        Route::get('/home/quote', [QuoteController::class, 'index'])->name('home.quote.section');
        Route::post('/home/quote/update', [QuoteController::class, 'update'])->name('home.quote.section.update');

        // Home Page — Our Story Section
        Route::get('/home/our-story', [OurStoryController::class, 'index'])->name('home.our_story.section');
        Route::post('/home/our-story/update', [OurStoryController::class, 'update'])->name('home.our_story.section.update');

        // Home Page — Testimonial Section
        Route::get('/home/testimonial', [TestimonialController::class, 'index'])->name('home.testimonial.section');
        Route::post('/home/testimonial/update', [TestimonialController::class, 'update'])->name('home.testimonial.section.update');

        // Home Page — Gallery Section
        Route::get('/home/gallery', [GalleryController::class, 'index'])->name('home.gallery.section');
        Route::post('/home/gallery/update', [GalleryController::class, 'update'])->name('home.gallery.section.update');

        // Home Page — Disclaimer Section
        Route::get('/home/disclaimer', [DisclaimerController::class, 'index'])->name('home.disclaimer.section');
        Route::post('/home/disclaimer/update', [DisclaimerController::class, 'update'])->name('home.disclaimer.section.update');

        // Home Page — We Believe Section
        Route::get('/home/we-believe', [WeBelieveController::class, 'index'])->name('home.we_believe.section');
        Route::post('/home/we-believe/update', [WeBelieveController::class, 'update'])->name('home.we_believe.section.update');

        // Home Page — Founder Statement Section
        Route::get('/home/founder-statement', [FounderStatementController::class, 'index'])->name('home.founder_statement.section');
        Route::post('/home/founder-statement/update', [FounderStatementController::class, 'update'])->name('home.founder_statement.section.update');

        // Our Story Page — Hero Section
        Route::get('/our-story/hero', [OurStoryPageController::class, 'index'])->name('our_story.hero.section');
        Route::post('/our-story/hero/update', [OurStoryPageController::class, 'update'])->name('our_story.hero.section.update');

        // How It Works Page — Hero Section
        Route::get('/how-it-works/hero', [HowItWorksPageController::class, 'index'])->name('how_it_works.hero.section');
        Route::post('/how-it-works/hero/update', [HowItWorksPageController::class, 'update'])->name('how_it_works.hero.section.update');

        // Terms & Conditions Page — Hero Section
        Route::get('/terms_&_conditions/hero', [TermsConditionsPageController::class, 'index'])->name('terms_conditions.hero.section');
        Route::post('/terms_&_conditions/hero/update', [TermsConditionsPageController::class, 'update'])->name('terms_conditions.hero.section.update');

        // Eligibility Page — Hero Section
        Route::get('/eligibility/hero', [EligibilityPageController::class, 'index'])->name('eligibility.hero.section');
        Route::post('/eligibility/hero/update', [EligibilityPageController::class, 'update'])->name('eligibility.hero.section.update');

        // Payment Policy Page — Hero Section
        Route::get('/payment-policy/hero', [PaymentPageController::class, 'index'])->name('payment_policy.hero.section');
        Route::post('/payment-policy/hero/update', [PaymentPageController::class, 'update'])->name('payment_policy.hero.section.update');

        // Tax Policy Page — Hero Section
        Route::get('/tax-policy/hero', [TaxPolicyPageController::class, 'index'])->name('tax_policy.hero.section');
        Route::post('/tax-policy/hero/update', [TaxPolicyPageController::class, 'update'])->name('tax_policy.hero.section.update');

        // Privacy Policy Page — Hero Section
        Route::get('/privacy_policy/hero', [PrivacyPolicyPageController::class, 'index'])->name('privacy_policy.hero.section');
        Route::post('/privacy_policy/hero/update', [PrivacyPolicyPageController::class, 'update'])->name('privacy_policy.hero.section.update');

        // Officer Compensation Page — Hero Section
        Route::get('/officer-compensation/hero', [OfficersCompPageController::class, 'index'])->name('officer_compensation.hero.section');
        Route::post('/officer-compensation/hero/update', [OfficersCompPageController::class, 'update'])->name('officer_compensation.hero.section.update');

        // Archive Page — Hero Section
        Route::get('/archive/hero', [ArchivePageController::class, 'index'])->name('archive.hero.section');
        Route::post('/archive/hero/update', [ArchivePageController::class, 'update'])->name('archive.hero.section.update');

        // Contact Us Page — Hero Section
        Route::get('/contact-us/hero', [ContactUsPageController::class, 'index'])->name('contact_us.hero.section');
        Route::post('/contact-us/hero/update', [ContactUsPageController::class, 'update'])->name('contact_us.hero.section.update');

        // Global — Top Bar Section
        Route::get('/topbar', [TopBarManageController::class, 'index'])->name('topbar.section');
        Route::post('/topbar/update', [TopBarManageController::class, 'update'])->name('topbar.section.update');

        // Global — Footer Section
        Route::get('/footer', [FooterManageController::class, 'index'])->name('footer.section');
        Route::post('/footer/update', [FooterManageController::class, 'update'])->name('footer.section.update');
    });

    // ------------------------------------------------------------------
    // Contact Us Submissions
    // Manages inbound contact form messages from website visitors.
    // ------------------------------------------------------------------
    Route::get('/person/contact-me', [ContactUsController::class, 'index'])->name('contact.me');
    Route::get('/person/contact-me/{id}', [ContactUsController::class, 'show'])->name('contact.me.show');
    Route::delete('/person/contact-me/delete/{id}', [ContactUsController::class, 'destroy'])->name('contact.me.delete');


    // ------------------------------------------------------------------
    // Weekly Draw Management
    // Handles creation, viewing, soft-deletion, restoration, and
    // permanent deletion of weekly draw records.
    // ------------------------------------------------------------------
    Route::prefix('weekly-draws')->name('weekly-draws.')->group(function () {
        Route::get('/', [WeeklyDrawController::class, 'index'])->name('index');
        Route::get('/data', [WeeklyDrawController::class, 'getData'])->name('data');
        Route::get('/{id}', [WeeklyDrawController::class, 'show'])->name('show');
        Route::get('/deleted/trashed', [WeeklyDrawController::class, 'trashed'])->name('trashed');
        Route::delete('/{id}', [WeeklyDrawController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-pause', [WeeklyDrawController::class, 'togglePause'])->name('toggle-pause');
        Route::post('/restore/{id}', [WeeklyDrawController::class, 'restore'])->name('restore');
        Route::delete('/force-delete/{id}', [WeeklyDrawController::class, 'forceDelete'])->name('force-delete');
    });

    // ------------------------------------------------------------------
    // Manual Draw Finalization
    // Emergency/manual finalization for accidental purposes.
    // ------------------------------------------------------------------
    Route::prefix('manual-finalize')->name('manual-finalize.')->group(function () {
        Route::get('/{id}', [ManualFinalizeController::class, 'show'])->name('show');
        Route::post('/{id}/finalize', [ManualFinalizeController::class, 'finalize'])->name('finalize');
    });


    // ------------------------------------------------------------------
    // Draw Winner Management
    // Manages draw winners including claim processing, payout handling,
    // multi-step identity verification, and data export.
    // ------------------------------------------------------------------
    Route::prefix('draw-winners')->name('draw-winners.')->group(function () {

        // Listing & Detail Views
        Route::get('/', [DrawWinnerController::class, 'index'])->name('index');
        Route::get('/show/{id}', [DrawWinnerController::class, 'show'])->name('show');

        // Claim Management
        Route::post('/mark-claimed/{id}', [DrawWinnerController::class, 'markClaimed'])->name('mark-claimed');

        // Payout Management
        Route::post('/process-payout/{id}', [DrawWinnerController::class, 'processPayout'])->name('process-payout');
        Route::post('/update-payout-status/{id}', [DrawWinnerController::class, 'updatePayoutStatus'])->name('update-payout-status');

        // Data Export
        Route::get('/export', [DrawWinnerController::class, 'export'])->name('export');

        // Winner Verification — Multi-step verification workflow
        Route::get('/{winner}/verify', [WinnerVerificationController::class, 'showVerificationPage'])->name('verify');
        Route::post('/{winner}/initiate-verification', [WinnerVerificationController::class, 'initiateVerification'])->name('initiate-verification');
        Route::post('/{winner}/verify-identity', [WinnerVerificationController::class, 'verifyIdentity'])->name('verify-identity');
        Route::post('/{winner}/verify-contact', [WinnerVerificationController::class, 'verifyContact'])->name('verify-contact');
        Route::post('/{winner}/verify-bank', [WinnerVerificationController::class, 'verifyBank'])->name('verify-bank');
        Route::post('/{winner}/approve-claim', [WinnerVerificationController::class, 'approveClaim'])->name('approve-claim');
        Route::post('/{winner}/reject-claim', [WinnerVerificationController::class, 'rejectClaim'])->name('reject-claim');
        Route::get('/{winner}/verification-status', [WinnerVerificationController::class, 'getVerificationStatus'])->name('verification-status');
    });

    // ------------------------------------------------------------------
    // Donor Management
    // Provides admin access to view and export donor records.
    // ------------------------------------------------------------------
    Route::prefix('donors')->name('donors.')->group(function () {
        Route::get('/', [DonationController::class, 'index'])->name('index');
        Route::get('/show/{id}', [DonationController::class, 'show'])->name('show');
        Route::get('/export', [DonationController::class, 'export'])->name('export');
    });


    // ------------------------------------------------------------------
    // Admin Chat (Web Interface)
    // Real-time messaging routes for the admin dashboard, including
    // conversation management, message actions, and typing indicators.
    // ------------------------------------------------------------------
    Route::prefix('chat')->name('chat.')->group(function () {

        // Chat Overview & Search
        Route::get('/', [ChatWebController::class, 'index'])->name('index');
        Route::get('/list', [ChatWebController::class, 'list'])->name('list');
        Route::get('/search', [ChatWebController::class, 'search'])->name('search');

        // Conversation Actions
        Route::get('/conversation/{receiver_id}', [ChatWebController::class, 'conversation'])->name('conversation');
        Route::post('/send/{receiver_id}', [ChatWebController::class, 'send'])->name('send');
        Route::get('/room/{receiver_id}', [ChatWebController::class, 'getRoom'])->name('room');

        // Message Management (Admin Only)
        Route::put('/message/{message_id}/edit', [ChatWebController::class, 'editMessage'])->name('message.edit');
        Route::delete('/message/{message_id}', [ChatWebController::class, 'deleteMessage'])->name('message.delete');
        Route::delete('/conversation/{receiver_id}', [ChatWebController::class, 'deleteChat'])->name('conversation.delete');

        // Presence & Read Status
        Route::post('/typing', [ChatWebController::class, 'typing'])->name('typing');
        Route::get('/seen/all/{receiver_id}', [ChatWebController::class, 'seenAll'])->name('seen.all');
        Route::get('/seen/single/{chat_id}', [ChatWebController::class, 'seenSingle'])->name('seen.single');
    });

    // ------------------------------------------------------------------
    // General Application Settings
    // Manages global application configuration such as Stripe keys
    // and other system-level settings.
    // ------------------------------------------------------------------
    Route::controller(SettingController::class)->group(function () {
        Route::get('setting/general', 'index')->name('setting.general.index');
        Route::patch('setting/general', 'update')->name('setting.general.update');
    });


    // ------------------------------------------------------------------
    // Stripe & Automation settings
    // ------------------------------------------------------------------
    Route::group(['middleware' => 'admin'], function () {
        Route::get('setting/stripe', [StripeSettingsController::class, 'index'])->name('setting.stripe.index');
        Route::patch('setting/stripe', [StripeSettingsController::class, 'update'])->name('setting.stripe.update');
        Route::patch('setting/stripe/percentage', [StripeSettingsController::class, 'updatePercentage'])->name('stripe.update-percentage');

        Route::get('setting/draw-automate', [DrawAutomateSettingController::class, 'index'])->name('setting.draw-automate.index');
        Route::post('setting/draw-automate', [DrawAutomateSettingController::class, 'update'])->name('setting.draw-automate.update');
    });
});


// ------------------------------------------------------------------
// Profile Settings
// Allows authenticated admins to manage their profile details,
// password, and profile picture.
// ------------------------------------------------------------------
Route::controller(ProfileController::class)->group(function () {
    Route::get('setting/profile', 'index')->name('setting.profile.index');
    Route::put('setting/profile/update', 'UpdateProfile')->name('setting.profile.update');
    Route::put('setting/profile/update/Password', 'UpdatePassword')->name('setting.profile.update.Password');
    Route::post('setting/profile/update/Picture', 'UpdateProfilePicture')->name('update.profile.picture');
});
