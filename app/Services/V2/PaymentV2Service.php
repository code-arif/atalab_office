<?php

namespace App\Services\V2;

use App\Mail\DonationConfirmation;
use App\Models\Donation;
use App\Models\DrawAutomateSetting;
use App\Models\DrawParticipant;
use App\Models\DrawWinner;
use App\Models\StripeSetting;
use App\Models\User;
use App\Models\UserWeekParticipation;
use App\Models\WeeklyDraw;
use App\Models\WinnerExclusion;
use App\Services\DonationService;
use App\Services\RegistrationService;
use App\Services\StripeService;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentV2Service
{
    protected StripeService $stripeService;
    protected RegistrationService $registrationService;
    protected DonationService $donationService;

    public function __construct(
        StripeService $stripeService,
        RegistrationService $registrationService,
        DonationService $donationService
    ) {
        $this->stripeService = $stripeService;
        $this->registrationService = $registrationService;
        $this->donationService = $donationService;
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    // -----------------------------------------------------------------------
    //  DONATION CREATION
    // -----------------------------------------------------------------------

    /**
     * V2: Create standard donation with card fingerprint tracking.
     * Mirrors V1 logic but routes through V2 namespace for isolated evolution.
     */
    public function createStandardDonation(
        int $userId,
        string $successUrl,
        string $cancelUrl,
        string $paymentMethodType = 'card',
        bool $isCover = false
    ): array {
        $lockKey = "v2_donation_lock:user_{$userId}";

        return Cache::lock($lockKey, 10)->block(5, function () use (
            $userId, $successUrl, $cancelUrl, $paymentMethodType, $isCover
        ) {
            return DB::transaction(function () use (
                $userId, $successUrl, $cancelUrl, $paymentMethodType, $isCover
            ) {
                $allowed = ['card', 'us_bank_account'];
                if (!in_array($paymentMethodType, $allowed, true)) {
                    throw new Exception('Invalid payment method type. Allowed: card, us_bank_account.');
                }

                $user = User::lockForUpdate()->findOrFail($userId);

                $currentDraw = WeeklyDraw::where('status', 'active')
                    ->where('is_paused', false)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Week eligibility
                $eligibility = $this->registrationService->canUserDonateToWeek($userId, $currentDraw->id);
                if (!$eligibility['eligible']) {
                    throw new Exception($eligibility['reason']);
                }

                // Ensure donor ID
                $this->registrationService->assignDonorIdIfNeeded($userId);
                $user = $user->fresh();

                // Amount & fee calculation
                $setting = StripeSetting::query()->first();
                $donationAmount = $setting ? (float) $setting->donation_amount : 25.00;
                if ($donationAmount <= 0) {
                    $donationAmount = 25.00;
                }

                $processingFee = $this->calculateProcessingFee($donationAmount, $paymentMethodType, $setting);
                $totalAmount = $isCover
                    ? round($donationAmount + $processingFee, 2)
                    : $donationAmount;

                // Stripe Checkout session
                $session = $this->stripeService->createCheckoutSession(
                    $totalAmount,
                    $currentDraw,
                    'standard',
                    $successUrl,
                    $cancelUrl,
                    $user,
                    $paymentMethodType,
                    $donationAmount,
                    $processingFee
                );

                // Unique donation ID
                $donationId = $this->donationService->generateUniqueDonationId();

                $donation = Donation::create([
                    'donation_id' => $donationId,
                    'user_id' => $user->id,
                    'week_id' => $currentDraw->id,
                    'amount' => $donationAmount,
                    'processing_fee' => $processingFee,
                    'total_amount' => $totalAmount,
                    'is_cover' => $isCover,
                    'stripe_payment_id' => $session->id,
                    'stripe_payment_status' => 'pending',
                    'is_eligible_for_draw' => false,
                    'payment_type' => 'standard',
                    'donated_at' => now(config('app.timezone')),
                    'attempt_number' => 1,
                ]);

                UserWeekParticipation::updateOrCreate(
                    ['user_id' => $user->id, 'week_id' => $currentDraw->id],
                    ['participated_at' => now(), 'has_donated' => false]
                );

                Log::info('[V2] Standard donation initiated', [
                    'donation_id' => $donation->id,
                    'donation_id_fmt' => $donation->donation_id,
                    'user_id' => $user->id,
                    'week_id' => $currentDraw->id,
                    'session_id' => $session->id,
                    'amount' => $donationAmount,
                    'processing_fee' => $processingFee,
                    'is_cover' => $isCover,
                    'total_amount' => $totalAmount,
                ]);

                return [
                    'checkout_url' => $session->url,
                    'session_id' => $session->id,
                    'donation_id' => $donation->id,
                    'donation_id_formatted' => $donation->donation_id,
                    'donor_id' => $user->donor_id,
                    'is_cover' => $isCover,
                    'processing_fee' => $processingFee,
                    'total_amount' => $totalAmount,
                ];
            });
        });
    }

    /**
     * V2: Create custom donation with card fingerprint tracking.
     */
    public function createCustomDonation(
        int $userId,
        float $amount,
        string $successUrl,
        string $cancelUrl,
        string $paymentMethodType = 'card'
    ): array {
        $lockKey = "v2_donation_lock:user_{$userId}";

        return Cache::lock($lockKey, 10)->block(5, function () use (
            $userId, $amount, $successUrl, $cancelUrl, $paymentMethodType
        ) {
            return DB::transaction(function () use (
                $userId, $amount, $successUrl, $cancelUrl, $paymentMethodType
            ) {
                $allowed = ['card', 'us_bank_account'];
                if (!in_array($paymentMethodType, $allowed, true)) {
                    throw new Exception('Invalid payment method type. Allowed: card, us_bank_account.');
                }

                if ($amount < 26) {
                    throw new Exception('Custom donation must be at least $26');
                }

                $user = User::lockForUpdate()->findOrFail($userId);

                $currentDraw = WeeklyDraw::where('status', 'active')
                    ->lockForUpdate()
                    ->firstOrFail();

                // Week eligibility
                $eligibility = $this->registrationService->canUserDonateToWeek($userId, $currentDraw->id);
                if (!$eligibility['eligible']) {
                    throw new Exception($eligibility['reason']);
                }

                // Check draw automation window (consistent with V1 custom donation behavior)
                if (!$this->isDonationAllowed()) {
                    throw new Exception('Donations are currently paused. Please try again on Monday at 12:00 AM.');
                }

                // Ensure donor ID
                $this->registrationService->assignDonorIdIfNeeded($userId);
                $user = $user->fresh();

                // Fee calculation
                $setting = StripeSetting::query()->first();
                $processingFee = $this->calculateProcessingFee($amount, $paymentMethodType, $setting);
                $totalAmount = round($amount + $processingFee, 2);

                $session = $this->stripeService->createCheckoutSession(
                    $totalAmount,
                    $currentDraw,
                    'custom',
                    $successUrl,
                    $cancelUrl,
                    $user,
                    $paymentMethodType,
                    $amount,
                    $processingFee
                );

                $donationId = $this->donationService->generateUniqueDonationId();

                $donation = Donation::create([
                    'donation_id' => $donationId,
                    'user_id' => $user->id,
                    'week_id' => $currentDraw->id,
                    'amount' => $amount,
                    'processing_fee' => $processingFee,
                    'total_amount' => $totalAmount,
                    'is_cover' => true, // custom always includes fees
                    'stripe_payment_id' => $session->id,
                    'stripe_payment_status' => 'pending',
                    'is_eligible_for_draw' => false,
                    'payment_type' => 'custom',
                    'donated_at' => now(config('app.timezone')),
                    'attempt_number' => 1,
                ]);

                UserWeekParticipation::updateOrCreate(
                    ['user_id' => $user->id, 'week_id' => $currentDraw->id],
                    ['participated_at' => now(), 'has_donated' => false]
                );

                Log::info('[V2] Custom donation initiated', [
                    'donation_id' => $donation->id,
                    'donation_id_fmt' => $donation->donation_id,
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'total_amount' => $totalAmount,
                    'processing_fee' => $processingFee,
                ]);

                return [
                    'checkout_url' => $session->url,
                    'session_id' => $session->id,
                    'donation_id' => $donation->id,
                    'donation_id_formatted' => $donation->donation_id,
                    'donor_id' => $user->donor_id,
                ];
            });
        });
    }

    // -----------------------------------------------------------------------
    //  PROCESSING FEE CALCULATION (server-side, never trust frontend)
    // -----------------------------------------------------------------------

    /**
     * Calculate the Stripe processing fee server-side.
     *
     * Formula (card): (amount × card_fee_percentage%) + card_fixed_fee
     * Formula (ACH):  flat fee from settings
     */
    public function calculateProcessingFee(
        float $amount,
        string $paymentMethodType = 'card',
        ?StripeSetting $setting = null
    ): float {
        if (!$setting) {
            $setting = StripeSetting::query()->first();
        }

        if ($paymentMethodType === 'us_bank_account') {
            return (float) ($setting?->ach_flat_fee ?? 0.00);
        }

        // Card processing fee: (amount × 2.9%) + $0.30
        $pct   = (float) ($setting?->card_fee_percentage ?? 2.9);
        $fixed = (float) ($setting?->card_fixed_fee ?? 0.30);

        return round(($amount * ($pct / 100)) + $fixed, 2);
    }

    // -----------------------------------------------------------------------
    //  CARD FINGERPRINT EXTRACTION
    // -----------------------------------------------------------------------

    /**
     * Extract and store the card fingerprint from a Stripe PaymentIntent.
     *
     * Flow:
     * 1. Retrieve the PaymentIntent (expand payment_method)
     * 2. Get the PaymentMethod details
     * 3. Extract the card fingerprint
     * 4. Store on the donation record
     */
    public function extractAndStoreCardFingerprint(string $paymentIntentId, int $donationId): void
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId, ['expand' => ['payment_method']]);

            $paymentMethod = $paymentIntent->payment_method;

            if (!$paymentMethod) {
                Log::warning('[V2] No payment method on PaymentIntent', [
                    'payment_intent_id' => $paymentIntentId,
                    'donation_id' => $donationId,
                ]);
                return;
            }

            $paymentMethodId = $paymentMethod->id;

            // Extract fingerprint based on payment method type
            $fingerprint = null;
            if (isset($paymentMethod->card) && isset($paymentMethod->card->fingerprint)) {
                $fingerprint = $paymentMethod->card->fingerprint;
            } elseif (isset($paymentMethod->us_bank_account)) {
                // ACH / US bank accounts don't have card fingerprints
                Log::info('[V2] Non-card payment method, skipping fingerprint', [
                    'payment_method_id' => $paymentMethodId,
                    'type' => $paymentMethod->type ?? 'unknown',
                ]);
            }

            // Store on donation
            $updateData = [
                'stripe_payment_method_id' => $paymentMethodId,
                'stripe_payment_intent_id' => $paymentIntentId,
            ];

            if ($fingerprint) {
                $updateData['card_fingerprint'] = $fingerprint;
            }

            Donation::where('id', $donationId)->update($updateData);

            Log::info('[V2] Card fingerprint stored', [
                'donation_id' => $donationId,
                'fingerprint' => $fingerprint ? substr($fingerprint, 0, 8) . '...' : 'N/A',
                'payment_method_id' => $paymentMethodId,
                'payment_intent_id' => $paymentIntentId,
            ]);
        } catch (Exception $e) {
            Log::error('[V2] Failed to extract card fingerprint', [
                'payment_intent_id' => $paymentIntentId,
                'donation_id' => $donationId,
                'error' => $e->getMessage(),
            ]);
            // Do not throw — fingerprint extraction should not break the payment flow
        }
    }

    // -----------------------------------------------------------------------
    //  DUPLICATE CARD DETECTION
    // -----------------------------------------------------------------------

    /**
     * Check if a card fingerprint has already been used for the given draw week.
     *
     * @param  string      $fingerprint  The 16-character Stripe card fingerprint
     * @param  int         $weekId       The weekly draw ID
     * @param  int|null    $excludeDonationId  Optional donation ID to exclude (the current donation)
     * @return array  ['is_duplicate' => bool, 'existing_donation' => array|null]
     */
    public function checkDuplicateCard(
        string $fingerprint,
        int $weekId,
        ?int $excludeDonationId = null
    ): array {
        $query = Donation::where('card_fingerprint', $fingerprint)
            ->where('week_id', $weekId)
            ->where('stripe_payment_status', 'completed');

        if ($excludeDonationId) {
            $query->where('id', '!=', $excludeDonationId);
        }

        $existing = $query->first();

        if ($existing) {
            Log::warning('[V2] Duplicate card detected', [
                'fingerprint' => substr($fingerprint, 0, 8) . '...',
                'week_id' => $weekId,
                'existing_donation_id' => $existing->id,
                'existing_user_id' => $existing->user_id,
            ]);

            return [
                'is_duplicate' => true,
                'existing_donation' => [
                    'id' => $existing->id,
                    'donation_id' => $existing->donation_id,
                    'user_id' => $existing->user_id,
                    'week_id' => $existing->week_id,
                    'amount' => $existing->amount,
                    'donated_at' => $existing->donated_at,
                ],
            ];
        }

        return [
            'is_duplicate' => false,
            'existing_donation' => null,
        ];
    }

    // -----------------------------------------------------------------------
    //  PAYMENT VERIFICATION
    // -----------------------------------------------------------------------

    /**
     * Verify payment via Stripe session lookup.
     * Also attempts to extract the card fingerprint from the PaymentIntent
     * and runs duplicate card detection.
     */
    public function verifyPayment(string $sessionId): Donation
    {
        return DB::transaction(function () use ($sessionId) {
            $session = Session::retrieve($sessionId);
            $donation = Donation::where('stripe_payment_id', $sessionId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($session->payment_status === 'paid' && !$donation->isCompleted()) {
                // Extract fingerprint before marking completed
                if ($session->payment_intent) {
                    $this->extractAndStoreCardFingerprint($session->payment_intent, $donation->id);
                    $donation = $donation->fresh();
                }

                // Duplicate check (post-completion — informational/logging only at this point)
                if ($donation->card_fingerprint) {
                    $duplicateCheck = $this->checkDuplicateCard(
                        $donation->card_fingerprint,
                        $donation->week_id,
                        $donation->id
                    );

                    if ($duplicateCheck['is_duplicate']) {
                        Log::warning('[V2] Duplicate card detected during verification', [
                            'donation_id' => $donation->id,
                            'fingerprint' => substr($donation->card_fingerprint, 0, 8) . '...',
                            'week_id' => $donation->week_id,
                        ]);
                    }
                }

                $donation->update([
                    'stripe_payment_status' => 'completed',
                    'stripe_charge_id' => $session->payment_intent,
                    'is_eligible_for_draw' => true,
                ]);

                UserWeekParticipation::where('user_id', $donation->user_id)
                    ->where('week_id', $donation->week_id)
                    ->update(['has_donated' => true]);

                $this->updateUserDonationStats($donation->user_id, $donation->amount);
                $this->updateWeeklyDrawStats($donation->week_id);
                $this->sendDonationConfirmationEmail($donation);
            }

            return $donation->fresh()->load('user');
        });
    }

    // -----------------------------------------------------------------------
    //  WEBHOOK HANDLERS (V2 Enhanced)
    // -----------------------------------------------------------------------

    /**
     * V2: Handle checkout.session.completed with fingerprint extraction
     * and duplicate card detection.
     */
    public function handleCheckoutCompleted($session): void
    {
        DB::transaction(function () use ($session) {
            $donation = Donation::where('stripe_payment_id', $session->id)
                ->lockForUpdate()
                ->first();

            if (!$donation || $donation->isCompleted()) {
                return;
            }

            // --- STEP 1: Extract and store card fingerprint ---
            if ($session->payment_intent) {
                $this->extractAndStoreCardFingerprint($session->payment_intent, $donation->id);
                $donation = $donation->fresh();
            }

            // --- STEP 2: Duplicate card detection ---
            if ($donation->card_fingerprint) {
                $duplicateCheck = $this->checkDuplicateCard(
                    $donation->card_fingerprint,
                    $donation->week_id,
                    $donation->id
                );

                if ($duplicateCheck['is_duplicate']) {
                    // Log the duplicate but still process the donation.
                    // The duplicate card user will still get their donation recorded,
                    // but we flag it for admin review.
                    Log::warning('[V2] Duplicate card detected in webhook', [
                        'new_donation_id' => $donation->id,
                        'existing_donation_id' => $duplicateCheck['existing_donation']['id'],
                        'week_id' => $donation->week_id,
                        'fingerprint' => substr($donation->card_fingerprint, 0, 8) . '...',
                    ]);
                }
            }

            // --- STEP 3: Complete the donation ---
            $donation->update([
                'stripe_payment_status' => 'completed',
                'stripe_charge_id' => $session->payment_intent,
                'is_eligible_for_draw'  => true,
            ]);

            UserWeekParticipation::where('user_id', $donation->user_id)
                ->where('week_id', $donation->week_id)
                ->update(['has_donated' => true]);

            $this->updateUserDonationStats($donation->user_id, $donation->amount);
            $this->updateWeeklyDrawStats($donation->week_id);
            $this->sendDonationConfirmationEmail($donation);

            Log::info('[V2] Checkout completed webhook processed with fingerprint tracking', [
                'donation_id' => $donation->id,
                'donation_id_fmt' => $donation->donation_id,
                'user_id' => $donation->user_id,
                'card_fingerprint' => $donation->card_fingerprint
                    ? substr($donation->card_fingerprint, 0, 8) . '...'
                    : 'N/A',
            ]);
        });
    }

    /**
     * V2: Handle payment_intent.succeeded with fingerprint extraction.
     */
    public function handlePaymentSucceeded($paymentIntent): void
    {
        DB::transaction(function () use ($paymentIntent) {
            $donation = Donation::where('stripe_charge_id', $paymentIntent->id)
                ->lockForUpdate()
                ->first();

            if (!$donation) {
                // Try matching by payment_intent_id if charge_id not set
                $donation = Donation::where('stripe_payment_intent_id', $paymentIntent->id)
                    ->lockForUpdate()
                    ->first();
            }

            if (!$donation || $donation->isCompleted()) {
                return;
            }

            // Extract fingerprint if not already done
            if (!$donation->card_fingerprint) {
                $this->extractAndStoreCardFingerprint($paymentIntent->id, $donation->id);
                $donation = $donation->fresh();
            }

            // Duplicate check
            if ($donation->card_fingerprint) {
                $duplicate = $this->checkDuplicateCard(
                    $donation->card_fingerprint,
                    $donation->week_id,
                    $donation->id
                );
                if ($duplicate['is_duplicate']) {
                    Log::warning('[V2] Duplicate card on payment_intent.succeeded', [
                        'donation_id' => $donation->id,
                        'week_id' => $donation->week_id,
                    ]);
                }
            }

            $donation->update([
                'stripe_payment_status' => 'completed',
                'is_eligible_for_draw' => true,
            ]);

            $this->updateWeeklyDrawStats($donation->week_id);

            if ($donation->wasChanged('stripe_payment_status')) {
                $this->sendDonationConfirmationEmail($donation);
            }
        });
    }

    /**
     * V2: Handle payment_intent.payment_failed.
     */
    public function handlePaymentFailed($paymentIntent): void
    {
        DB::transaction(function () use ($paymentIntent) {
            $donation = Donation::where('stripe_charge_id', $paymentIntent->id)
                ->lockForUpdate()
                ->first();

            if ($donation) {
                $donation->update([
                    'stripe_payment_status' => 'failed',
                    'is_eligible_for_draw' => false,
                ]);

                Log::info('[V2] Payment failed recorded', [
                    'donation_id' => $donation->id,
                    'payment_intent' => $paymentIntent->id,
                ]);
            }
        });
    }

    /**
     * Check payment status.
     */
    public function checkPaymentStatus(string $paymentId): array
    {
        $donation = Donation::where('stripe_payment_id', $paymentId)->firstOrFail();

        return [
            'status' => $donation->stripe_payment_status,
            'donation_id' => $donation->donation_id,
            'amount' => $donation->amount,
            'processing_fee' => $donation->processing_fee,
            'total_amount' => $donation->total_amount,
            'is_cover' => $donation->is_cover,
            'card_fingerprint' => $donation->card_fingerprint
                ? substr($donation->card_fingerprint, 0, 8) . '...'
                : null,
            'has_duplicate_card' => $donation->card_fingerprint
                ? $this->checkDuplicateCard($donation->card_fingerprint, $donation->week_id, $donation->id)['is_duplicate']
                : false,
            'donated_at' => $donation->donated_at,
            'is_eligible_for_draw'  => $donation->is_eligible_for_draw,
        ];
    }

    // -----------------------------------------------------------------------
    //  DRAW AUTOMATION WINDOW CHECK
    // -----------------------------------------------------------------------

    /**
     * Check if donations are allowed based on the draw automation schedule.
     * Mirrors V1 DonationService::isDonationAllowed() for behavioral consistency.
     */
    protected function isDonationAllowed(): bool
    {
        $settings = DrawAutomateSetting::first();

        if (!$settings) {
            return true;
        }

        $now = now(config('app.timezone'));

        $startDayConstant = constant('\\Carbon\\Carbon::' . strtoupper($settings->draw_start_day));
        $endDayConstant = constant('\\Carbon\\Carbon::' . strtoupper($settings->draw_end_day));

        $startTime = Carbon::parse($settings->draw_start_time);
        $endTime = Carbon::parse($settings->draw_end_time);

        $drawStart = $now->copy()
            ->startOfWeek($startDayConstant)
            ->setTime($startTime->hour, $startTime->minute, 0);

        $drawEnd = $drawStart->copy()
            ->endOfWeek($endDayConstant)
            ->setTime($endTime->hour, $endTime->minute, 0);

        return $now->gte($drawStart) && $now->lt($drawEnd);
    }

    // -----------------------------------------------------------------------
    //  INTERNAL HELPERS
    // -----------------------------------------------------------------------

    protected function updateUserDonationStats(int $userId, float $amount): void
    {
        DB::table('users')
            ->where('id', $userId)
            ->update([
                'total_donations_count' => DB::raw('total_donations_count + 1'),
                'lifetime_donation_amount' => DB::raw("lifetime_donation_amount + {$amount}"),
                'last_donation_at' => now(),
            ]);
    }

    protected function updateWeeklyDrawStats(int $weekId): void
    {
        $draw = WeeklyDraw::lockForUpdate()->find($weekId);
        if (!$draw) {
            return;
        }

        $stats = Donation::where('week_id', $weekId)
            ->where('stripe_payment_status', 'completed')
            ->selectRaw('SUM(amount) as total, COUNT(DISTINCT user_id) as participants')
            ->first();

        $newTotal = $stats->total ?? 0;
        $newParticipantsCount = $stats->participants ?? 0;

        $previousDraw = WeeklyDraw::whereIn('status', ['completed', 'claiming'])
            ->where('id', '<', $draw->id)
            ->orderBy('id', 'desc')
            ->first();

        if ($previousDraw) {
            $excludedUserIds = WinnerExclusion::where('is_active', true)
                ->where('exclusion_ends_at', '>', now())
                ->pluck('user_id')
                ->unique()
                ->toArray();

            $rolloverParticipants = DrawParticipant::where('weekly_draw_id', $previousDraw->id)
                ->whereNotIn('user_id', $excludedUserIds)
                ->get();

            $previousWinners = DrawWinner::where('weekly_draw_id', $previousDraw->id)
                ->pluck('user_id')
                ->toArray();

            $validRollovers = $rolloverParticipants->filter(fn($p) => !in_array($p->user_id, $previousWinners));

            $rolloverCount = $validRollovers->count();
            $rolloverTotal = Donation::whereIn('id', $validRollovers->pluck('donation_id'))->sum('amount');

            $newTotal += $rolloverTotal;
            $newParticipantsCount += $rolloverCount;
        }

        $draw->update([
            'total_pool' => $newTotal,
            'total_participants' => $newParticipantsCount,
            'last_stats_update' => now(),
        ]);
    }

    protected function sendDonationConfirmationEmail(Donation $donation): void
    {
        try {
            $donation->load(['user', 'weeklyDraw']);
            Mail::to($donation->user->email)
                ->send(new DonationConfirmation($donation));

            Log::info('[V2] Donation confirmation email sent', [
                'donation_id' => $donation->id,
                'user_id' => $donation->user_id,
                'email' => $donation->user->email,
            ]);
        } catch (Exception $e) {
            Log::error('[V2] Failed to send donation confirmation email', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
