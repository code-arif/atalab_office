<?php

namespace App\Services;

use App\Events\DonationCreated;
use App\Mail\DonationConfirmation;
use App\Models\Donation;
use App\Models\StripeSetting;
use App\Models\User;
use App\Models\UserWeekParticipation;
use App\Models\WeeklyDraw;
use Exception;
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
     * FIXED: Create standard donation with donor_id assignment BEFORE payment
     */
    public function createStandardDonation(int $userId, string $successUrl, string $cancelUrl, string $paymentMethodType = 'card'): array
    {
        $lockKey = "donation_lock:user_{$userId}";

        return Cache::lock($lockKey, 10)->block(5, function () use ($userId, $successUrl, $cancelUrl, $paymentMethodType) {
            return DB::transaction(function () use ($userId, $successUrl, $cancelUrl, $paymentMethodType) {
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

                if (!$this->isDonationAllowed()) {
                    throw new Exception('Donations are currently paused. Please try again on Monday at 12:00 AM.');
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
                $paymentAmount = $setting ? floatval($setting->donation_amount) : 25.00;

                if ($paymentAmount <= 0) {
                    $paymentAmount = 25.00; // fallback if missing
                }

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

                // Create Stripe checkout session
                $session = $this->stripeService->createCheckoutSession(
                    $totalAmount,
                    $currentDraw,
                    'standard',
                    $successUrl,
                    $cancelUrl,
                    $user,
                    $paymentMethodType,
                    $paymentAmount,
                    $processingFee
                );

                // Create donation record
                $donation = Donation::create([
                    'user_id' => $user->id,
                    'week_id' => $currentDraw->id,
                    'amount' => $paymentAmount,
                    'processing_fee' => $processingFee,
                    'total_amount' => $totalAmount,
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
                    'user_id' => $user->id,
                    'donor_id' => $user->donor_id,
                    'week_id' => $currentDraw->id,
                    'session_id' => $session->id,
                    'total_amount' => $totalAmount,
                    'processing_fee' => $processingFee,
                ]);

                return [
                    'checkout_url' => $session->url,
                    'session_id' => $session->id,
                    'donation_id' => $donation->id,
                    'donor_id' => $user->donor_id,
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
                $setting = \App\Models\StripeSetting::query()->first();
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

                $donation = Donation::create([
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
                    'donor_id' => $user->donor_id,
                ];
            });
        });
    }

    protected function isDonationAllowed(): bool
    {
        $now = now(config('app.timezone'));

        if ($now->isSunday() && $now->hour >= 17) {
            return false;
        }

        if ($now->isMonday() && $now->hour < 0) {
            return false;
        }

        return true;
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
            'amount' => $donation->amount,
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
