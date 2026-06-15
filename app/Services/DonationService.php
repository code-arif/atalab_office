<?php

namespace App\Services;

use App\Events\DonationCreated;
use App\Mail\DonationConfirmation;
use App\Models\Donation;
use App\Models\DrawAutomateSetting;
use App\Models\StripeSetting;
use App\Models\User;
use App\Models\UserWeekParticipation;
use App\Models\WeeklyDraw;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class DonationService
{
    protected $stripeService;
    protected $registrationService;

    public function __construct(StripeService $stripeService, RegistrationService $registrationService)
    {
        $this->stripeService = $stripeService;
        $this->registrationService = $registrationService;
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Generate a unique donation ID with sequential format: DONATION-0000000001
     */
    public function generateUniqueDonationId(): string
    {
        $maxRetries = 5;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                return DB::transaction(function () {
                    // Lock for atomic operation
                    $lastDonation = Donation::whereNotNull('donation_id')
                        ->orderByRaw('CAST(SUBSTRING(donation_id, 10) AS UNSIGNED) DESC')
                        ->lockForUpdate()
                        ->first();

                    if (!$lastDonation || !$lastDonation->donation_id) {
                        $nextNumber = 1;
                    } else {
                        $lastNumber = (int) substr($lastDonation->donation_id, 9);
                        $nextNumber = $lastNumber + 1;
                    }

                    $donationId = 'DONATION-' . str_pad($nextNumber, 10, '0', STR_PAD_LEFT);

                    // Verify uniqueness
                    $exists = Donation::where('donation_id', $donationId)->exists();
                    if ($exists) {
                        throw new Exception('Donation ID collision detected');
                    }

                    return $donationId;
                });
            } catch (Exception $e) {
                $attempt++;
                Log::warning('Donation ID generation retry', [
                    'attempt' => $attempt,
                    'error' => $e->getMessage()
                ]);

                if ($attempt >= $maxRetries) {
                    throw new Exception('Failed to generate unique donation ID after ' . $maxRetries . ' attempts');
                }

                usleep(100000); // 100ms delay before retry
            }
        }

        throw new Exception('Failed to generate donation ID');
    }

    /**
     * FIXED: Create standard donation with donor_id assignment BEFORE payment
     *
     * Supports is_cover (cover processing fees) feature:
     * - When is_cover = true,  total_amount = donation_amount + processing_fee (donor pays both)
     * - When is_cover = false, total_amount = donation_amount only (organization absorbs the fee)
     */
    public function createStandardDonation(int $userId, string $successUrl, string $cancelUrl, string $paymentMethodType = 'card', bool $isCover = false): array
    {
        $lockKey = "donation_lock:user_{$userId}";

        return Cache::lock($lockKey, 10)->block(5, function () use ($userId, $successUrl, $cancelUrl, $paymentMethodType, $isCover) {
            return DB::transaction(function () use ($userId, $successUrl, $cancelUrl, $paymentMethodType, $isCover) {
                $allowedPaymentMethods = ['card', 'us_bank_account'];
                if (!in_array($paymentMethodType, $allowedPaymentMethods, true)) {
                    throw new Exception('Invalid payment method type. Allowed values: card, us_bank_account.');
                }

                $user = User::lockForUpdate()->find($userId);

                if (!$user) {
                    throw new Exception('User not found. Please complete registration first.');
                }

                $currentDraw = WeeklyDraw::where('status', 'active')
                    ->where('is_paused', false)
                    ->lockForUpdate()
                    ->first();

                if (!$currentDraw) {
                    throw new Exception('No active draw available. Donations are paused.');
                }

                // Check week eligibility
                $eligibility = $this->registrationService->canUserDonateToWeek($userId, $currentDraw->id);

                if (!$eligibility['eligible']) {
                    throw new Exception($eligibility['reason']);
                }

                $donorId = $this->registrationService->assignDonorIdIfNeeded($userId);

                Log::info('Donor ID ensured before payment', [
                    'user_id' => $userId,
                    'donor_id' => $donorId,
                ]);

                $user = $user->fresh();

                // Dynamic amount from DB
                $setting = StripeSetting::query()->first();
                $donationAmount = $setting ? floatval($setting->donation_amount) : 25.00;

                if ($donationAmount <= 0) {
                    $donationAmount = 25.00; // fallback if missing
                }

                // Calculate processing fee based on payment method
                $processingFee = 0.00;
                if ($paymentMethodType === 'us_bank_account') {
                    $achFlatFee = floatval($setting?->ach_flat_fee ?? 0.00);
                    $processingFee = $achFlatFee;
                } elseif ($paymentMethodType === 'card') {
                    $cardPct = floatval($setting?->card_fee_percentage ?? 2.9);
                    $cardFixed = floatval($setting?->card_fixed_fee ?? 0.30);
                    $processingFee = round(($donationAmount * ($cardPct / 100)) + $cardFixed, 2);
                }

                // Calculate total amount based on whether donor covers fees
                // is_cover = true:  donor pays donation + processing fee
                // is_cover = false: donor pays donation only (org absorbs fee)
                $paymentAmount = $isCover
                    ? round($donationAmount + $processingFee, 2)
                    : $donationAmount;

                // Create Stripe checkout session with the final payment amount
                $session = $this->stripeService->createCheckoutSession(
                    $paymentAmount,
                    $currentDraw,
                    'standard',
                    $successUrl,
                    $cancelUrl,
                    $user,
                    $paymentMethodType,
                    $donationAmount,
                    $processingFee
                );

                // Generate unique donation ID
                $donationId = $this->generateUniqueDonationId();

                // Create donation record
                $donation = Donation::create([
                    'donation_id' => $donationId,
                    'user_id' => $user->id,
                    'week_id' => $currentDraw->id,
                    'amount' => $donationAmount,
                    'processing_fee' => $processingFee,
                    'total_amount' => $paymentAmount,
                    'is_cover' => $isCover,
                    'stripe_payment_id' => $session->id,
                    'stripe_payment_status' => 'pending',
                    'is_eligible_for_draw' => false,
                    'payment_type' => 'standard',
                    'donated_at' => now(config('app.timezone')),
                    'attempt_number' => 1,
                ]);

                // Track participation
                UserWeekParticipation::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'week_id' => $currentDraw->id,
                    ],
                    [
                        'participated_at' => now(),
                        'has_donated' => false,
                    ]
                );

                Log::info('Standard donation initiated', [
                    'donation_id' => $donation->id,
                    'donation_id_formatted' => $donation->donation_id,
                    'user_id' => $user->id,
                    'donor_id' => $user->donor_id,
                    'week_id' => $currentDraw->id,
                    'session_id' => $session->id,
                    'donation_amount' => $donationAmount,
                    'processing_fee' => $processingFee,
                    'is_cover' => $isCover,
                    'total_amount' => $paymentAmount,
                ]);

                return [
                    'checkout_url' => $session->url,
                    'session_id' => $session->id,
                    'donation_id' => $donation->id,
                    'donation_id_formatted' => $donation->donation_id,
                    'donor_id' => $user->donor_id,
                    'is_cover' => $isCover,
                    'processing_fee' => $processingFee,
                    'total_amount' => $paymentAmount,
                ];
            });
        });
    }

    /**
     * FIXED: Create custom donation with donor_id assignment BEFORE payment
     */
    public function createCustomDonation(int $userId, float $amount, string $successUrl, string $cancelUrl, string $paymentMethodType = 'card'): array
    {
        $lockKey = "donation_lock:user_{$userId}";

        return Cache::lock($lockKey, 10)->block(5, function () use ($userId, $amount, $successUrl, $cancelUrl, $paymentMethodType) {
            return DB::transaction(function () use ($userId, $amount, $successUrl, $cancelUrl, $paymentMethodType) {
                $allowedPaymentMethods = ['card', 'us_bank_account'];
                if (!in_array($paymentMethodType, $allowedPaymentMethods, true)) {
                    throw new Exception('Invalid payment method type. Allowed values: card, us_bank_account.');
                }

                if ($amount < 26) {
                    throw new Exception('Custom donation must be at least $26');
                }

                $user = User::lockForUpdate()->find($userId);

                if (!$user) {
                    throw new Exception('User not found. Please complete registration first.');
                }

                $currentDraw = WeeklyDraw::where('status', 'active')
                    ->lockForUpdate()
                    ->first();

                if (!$currentDraw) {
                    throw new Exception('No active draw available. Donations are paused.');
                }

                if (!$this->isDonationAllowed()) {
                    throw new Exception('Donations are currently paused. Please try again on Monday at 12:00 AM.');
                }

                // Check eligibility
                $eligibility = $this->registrationService->canUserDonateToWeek($userId, $currentDraw->id);

                if (!$eligibility['eligible']) {
                    throw new Exception($eligibility['reason']);
                }

                // Assign donor_id BEFORE creating Stripe session
                $donorId = $this->registrationService->assignDonorIdIfNeeded($userId);

                Log::info('Donor ID ensured before payment', [
                    'user_id' => $userId,
                    'donor_id' => $donorId,
                ]);

                // Reload user
                $user = $user->fresh();

                // Get setting
                $setting = StripeSetting::query()->first();
                $paymentAmount = $amount;

                // Calculate processing fee
                $processingFee = 0.00;
                if ($paymentMethodType === 'us_bank_account') {
                    $achFlatFee = floatval($setting?->ach_flat_fee ?? 0.00);
                    $processingFee = $achFlatFee;
                } elseif ($paymentMethodType === 'card') {
                    $cardPct = floatval($setting?->card_fee_percentage ?? 2.9);
                    $cardFixed = floatval($setting?->card_fixed_fee ?? 0.30);
                    $processingFee = round(($paymentAmount * ($cardPct / 100)) + $cardFixed, 2);
                }

                $totalAmount = round($paymentAmount + $processingFee, 2);

                $session = $this->stripeService->createCheckoutSession(
                    $totalAmount,
                    $currentDraw,
                    'custom',
                    $successUrl,
                    $cancelUrl,
                    $user,
                    $paymentMethodType,
                    $paymentAmount,
                    $processingFee
                );

                // Generate unique donation ID
                $donationId = $this->generateUniqueDonationId();

                $donation = Donation::create([
                    'donation_id' => $donationId,
                    'user_id' => $user->id,
                    'week_id' => $currentDraw->id,
                    'amount' => $paymentAmount,
                    'processing_fee' => $processingFee,
                    'total_amount' => $totalAmount,
                    'stripe_payment_id' => $session->id,
                    'stripe_payment_status' => 'pending',
                    'is_eligible_for_draw' => false,
                    'payment_type' => 'custom',
                    'donated_at' => now(config('app.timezone')),
                    'attempt_number' => 1,
                ]);

                UserWeekParticipation::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'week_id' => $currentDraw->id,
                    ],
                    [
                        'participated_at' => now(),
                        'has_donated' => false,
                    ]
                );

                Log::info('Custom donation initiated', [
                    'donation_id' => $donation->id,
                    'donation_id_formatted' => $donation->donation_id,
                    'user_id' => $user->id,
                    'donor_id' => $user->donor_id,
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

    protected function isDonationAllowed(): bool
    {
        $settings = DrawAutomateSetting::first();

        // Default to allowing donations if no settings exist
        if (!$settings) {
            return true;
        }

        $now = now(config('app.timezone'));

        $startDayConstant = constant('\\Carbon\\Carbon::' . strtoupper($settings->draw_start_day));
        $endDayConstant = constant('\\Carbon\\Carbon::' . strtoupper($settings->draw_end_day));

        $startTime = Carbon::parse($settings->draw_start_time);
        $endTime = Carbon::parse($settings->draw_end_time);

        // Calculate the current draw period from DB settings
        $drawStart = $now->copy()
            ->startOfWeek($startDayConstant)
            ->setTime($startTime->hour, $startTime->minute, 0);

        $drawEnd = $drawStart->copy()
            ->endOfWeek($endDayConstant)
            ->setTime($endTime->hour, $endTime->minute, 0);

        // Donations allowed only within the active draw window (exclusive end boundary)
        return $now->gte($drawStart) && $now->lt($drawEnd);
    }

    /**
     * FIXED: Webhook handler - No donor_id assignment here anymore
     */
    public function verifyPayment(string $sessionId): Donation
    {
        return DB::transaction(function () use ($sessionId) {
            $session = Session::retrieve($sessionId);

            $donation = Donation::where('stripe_payment_id', $sessionId)
                ->lockForUpdate()
                ->first();

            if (!$donation) {
                throw new Exception('Donation not found');
            }

            if ($session->payment_status === 'paid' && $donation->stripe_payment_status !== 'completed') {
                $donation->update([
                    'stripe_payment_status' => 'completed',
                    'stripe_charge_id' => $session->payment_intent,
                    'is_eligible_for_draw' => true,
                ]);

                // Update participation status
                UserWeekParticipation::where('user_id', $donation->user_id)
                    ->where('week_id', $donation->week_id)
                    ->update(['has_donated' => true]);

                // Update user stats
                $this->updateUserDonationStats($donation->user_id, $donation->amount);

                // Update weekly draw stats
                $this->updateWeeklyDrawStats($donation->week_id);

                // SEND CONFIRMATION EMAIL
                $this->sendDonationConfirmationEmail($donation);

                Log::info('Payment verified - webhook completed', [
                    'donation_id' => $donation->id,
                    'donation_id_formatted' => $donation->donation_id,
                    'user_id' => $donation->user_id,
                    'donor_id' => $donation->user->donor_id,
                ]);
            }

            return $donation->fresh()->load('user');
        });
    }

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

        if ($draw) {
            $stats = Donation::where('week_id', $weekId)
                ->where('stripe_payment_status', 'completed')
                ->selectRaw('SUM(amount) as total, COUNT(DISTINCT user_id) as participants')
                ->first();

            $newTotal = $stats->total ?? 0;
            $newParticipantsCount = $stats->participants ?? 0;

            // Calculate rollover from previous week
            $previousDraw = WeeklyDraw::whereIn('status', ['completed', 'claiming'])
                ->where('id', '<', $draw->id)
                ->orderBy('id', 'desc')
                ->first();

            if ($previousDraw) {
                // Get excluded user IDs to not count them
                $excludedUserIds = \App\Models\WinnerExclusion::where('is_active', true)
                    ->where('exclusion_ends_at', '>', now())
                    ->pluck('user_id')
                    ->unique()
                    ->toArray();

                $rolloverParticipants = \App\Models\DrawParticipant::where('weekly_draw_id', $previousDraw->id)
                    ->whereNotIn('user_id', $excludedUserIds)
                    ->get();

                $previousWinners = \App\Models\DrawWinner::where('weekly_draw_id', $previousDraw->id)->pluck('user_id')->toArray();

                $validRollovers = $rolloverParticipants->filter(function ($p) use ($previousWinners) {
                    return !in_array($p->user_id, $previousWinners);
                });

                $rolloverCount = $validRollovers->count();
                $rolloverTotal = \App\Models\Donation::whereIn('id', $validRollovers->pluck('donation_id'))->sum('amount');

                $newTotal += $rolloverTotal;
                $newParticipantsCount += $rolloverCount;
            }

            $draw->update([
                'total_pool' => $newTotal,
                'total_participants' => $newParticipantsCount,
                'last_stats_update' => now(),
            ]);
        }
    }

    /**
     * Send donation confirmation email
     */
    protected function sendDonationConfirmationEmail(Donation $donation): void
    {
        try {
            // Load necessary relationships
            $donation->load(['user', 'weeklyDraw']);

            // Send email
            Mail::to($donation->user->email)
                ->send(new DonationConfirmation($donation));

            Log::info('Donation confirmation email sent', [
                'donation_id' => $donation->id,
                'donation_id_formatted' => $donation->donation_id,
                'user_id' => $donation->user_id,
                'name'=> $donation->user->name,
                'email' => $donation->user->email,
            ]);
        } catch (Exception $e) {
            // Log error but don't fail the webhook
            Log::error('Failed to send donation confirmation email', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * FIXED: Webhook - checkout completed
     */
    public function handleCheckoutCompleted($session): void
    {
        DB::transaction(function () use ($session) {
            $donation = Donation::where('stripe_payment_id', $session->id)
                ->lockForUpdate()
                ->first();

            if ($donation && $donation->stripe_payment_status !== 'completed') {
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

                // SEND CONFIRMATION EMAIL
                $this->sendDonationConfirmationEmail($donation);

                Log::info('Checkout completed webhook', [
                    'donation_id' => $donation->id,
                    'donation_id_formatted' => $donation->donation_id,
                    'user_id' => $donation->user_id,
                    'donor_id' => $donation->user->donor_id,
                ]);
            }
        });
    }

    public function handlePaymentSucceeded($paymentIntent): void
    {
        DB::transaction(function () use ($paymentIntent) {
            $donation = Donation::where('stripe_charge_id', $paymentIntent->id)
                ->lockForUpdate()
                ->first();

            if ($donation) {
                $donation->update([
                    'stripe_payment_status' => 'completed',
                    'is_eligible_for_draw' => true,
                ]);

                $this->updateWeeklyDrawStats($donation->week_id);

                // SEND CONFIRMATION EMAIL (if not already sent)
                if ($donation->wasChanged('stripe_payment_status')) {
                    $this->sendDonationConfirmationEmail($donation);
                }
            }
        });
    }

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
            }
        });
    }

    public function checkPaymentStatus(string $paymentId): array
    {
        $donation = Donation::where('stripe_payment_id', $paymentId)->first();

        if (!$donation) {
            throw new Exception('Payment not found');
        }

        return [
            'status' => $donation->stripe_payment_status,
            'donation_id' => $donation->donation_id,
            'amount' => $donation->amount,
            'processing_fee' => $donation->processing_fee,
            'total_amount' => $donation->total_amount,
            'is_cover' => $donation->is_cover,
            'donated_at' => $donation->donated_at,
            'is_eligible_for_draw' => $donation->is_eligible_for_draw,
        ];
    }

    public function getAllDonations(int $page = 1, int $perPage = 50)
    {
        return Donation::with(['user', 'weeklyDraw'])
            ->orderBy('donated_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getDonationsByWeek(int $weekId)
    {
        return Donation::with('user')
            ->where('week_id', $weekId)
            ->orderBy('donated_at', 'desc')
            ->get();
    }
}
