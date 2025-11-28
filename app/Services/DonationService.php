<?php

namespace App\Services;

use Exception;
use Stripe\Stripe;
use App\Models\User;
use App\Models\Donation;
use App\Models\WeeklyDraw;
use Stripe\Checkout\Session;
use App\Events\DonationCreated;
use App\Models\UserWeekParticipation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

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
     * ENHANCED: Create standard $25 donation with concurrency protection
     */
    public function createStandardDonation(int $userId, string $successUrl, string $cancelUrl): array
    {
        // Use distributed lock to prevent race conditions
        $lockKey = "donation_lock:user_{$userId}";

        return Cache::lock($lockKey, 10)->block(5, function () use ($userId, $successUrl, $cancelUrl) {
            return DB::transaction(function () use ($userId, $successUrl, $cancelUrl) {
                // Validate user
                $user = User::lockForUpdate()->find($userId);

                if (!$user) {
                    throw new Exception('User not verified. Please complete registration first.');
                }

                // Get current active draw
                $currentDraw = WeeklyDraw::where('status', 'active')
                    ->lockForUpdate()
                    ->first();

                if (!$currentDraw) {
                    throw new Exception('No active draw available. Donations are paused.');
                }

                // Check donation time window
                if (!$this->isDonationAllowed()) {
                    throw new Exception('Donations are currently paused. Please try again on Monday at 12:00 AM.');
                }

                // CRITICAL: Check if user can donate this week
                $eligibility = $this->registrationService->canUserDonateToWeek($userId, $currentDraw->id);

                if (!$eligibility['eligible']) {
                    throw new Exception($eligibility['reason']);
                }

                // Create Stripe checkout session
                $session = $this->stripeService->createCheckoutSession(
                    25.00,
                    $currentDraw,
                    'standard',
                    $successUrl,
                    $cancelUrl,
                    $user
                );

                // Create donation record
                $donation = Donation::create([
                    'user_id' => $user->id,
                    'week_id' => $currentDraw->id,
                    'amount' => 25.00,
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
                        'has_donated' => false, // Will be true after payment completion
                    ]
                );

                Log::info('Standard donation initiated', [
                    'donation_id' => $donation->id,
                    'user_id' => $user->id,
                    'week_id' => $currentDraw->id,
                    'session_id' => $session->id,
                ]);

                event(new DonationCreated($donation));

                return [
                    'checkout_url' => $session->url,
                    'session_id' => $session->id,
                    'donation_id' => $donation->id
                ];
            });
        });
    }

    /**
     * ENHANCED: Create custom amount donation with concurrency protection
     */
    public function createCustomDonation(int $userId, float $amount, string $successUrl, string $cancelUrl): array
    {
        $lockKey = "donation_lock:user_{$userId}";

        return Cache::lock($lockKey, 10)->block(5, function () use ($userId, $amount, $successUrl, $cancelUrl) {
            return DB::transaction(function () use ($userId, $amount, $successUrl, $cancelUrl) {
                if ($amount < 26) {
                    throw new Exception('Custom donation must be at least $26');
                }

                $user = User::lockForUpdate()->find($userId);

                if (!$user || !$user->email_verified_at || !$user->phone_verified_at) {
                    throw new Exception('User not verified. Please complete registration first.');
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

                $session = $this->stripeService->createCheckoutSession(
                    $amount,
                    $currentDraw,
                    'custom',
                    $successUrl,
                    $cancelUrl,
                    $user
                );

                $donation = Donation::create([
                    'user_id' => $user->id,
                    'week_id' => $currentDraw->id,
                    'amount' => $amount,
                    'stripe_payment_id' => $session->id,
                    'stripe_payment_status' => 'pending',
                    'is_eligible_for_draw' => false,
                    'payment_type' => 'custom',
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

                Log::info('Custom donation initiated', [
                    'donation_id' => $donation->id,
                    'user_id' => $user->id,
                    'amount' => $amount,
                ]);

                return [
                    'checkout_url' => $session->url,
                    'session_id' => $session->id,
                    'donation_id' => $donation->id
                ];
            });
        });
    }

    /**
     * Check if donation is allowed based on time window
     */
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
     * ENHANCED: Verify payment with atomic updates
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

                // Assign donor_id on first donation
                $this->registrationService->assignDonorId($donation->user_id);

                // Update user stats
                $this->updateUserDonationStats($donation->user_id, $donation->amount);

                // Update weekly draw stats
                $this->updateWeeklyDrawStats($donation->week_id);

                Log::info('Payment verified', [
                    'donation_id' => $donation->id,
                    'user_id' => $donation->user_id,
                ]);
            }

            return $donation->fresh()->load('user');
        });
    }

    /**
     * Update user donation statistics
     */
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

    /**
     * Update weekly draw statistics with atomic operations
     */
    protected function updateWeeklyDrawStats(int $weekId): void
    {
        $draw = WeeklyDraw::lockForUpdate()->find($weekId);

        if ($draw) {
            $stats = Donation::where('week_id', $weekId)
                ->where('stripe_payment_status', 'completed')
                ->selectRaw('SUM(amount) as total, COUNT(DISTINCT user_id) as participants')
                ->first();

            $draw->update([
                'total_pool' => $stats->total ?? 0,
                'total_participants' => $stats->participants ?? 0,
                'last_stats_update' => now(),
            ]);
        }
    }

    /**
     * Handle Stripe checkout completed webhook
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

                // Update participation
                UserWeekParticipation::where('user_id', $donation->user_id)
                    ->where('week_id', $donation->week_id)
                    ->update(['has_donated' => true]);

                // Assign donor_id
                $this->registrationService->assignDonorId($donation->user_id);

                // Update stats
                $this->updateUserDonationStats($donation->user_id, $donation->amount);
                $this->updateWeeklyDrawStats($donation->week_id);

                Log::info('Checkout completed', [
                    'donation_id' => $donation->id,
                    'user_id' => $donation->user_id
                ]);
            }
        });
    }

    /**
     * Handle payment succeeded webhook
     */
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
            }
        });
    }

    /**
     * Handle payment failed webhook
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
            }
        });
    }

    /**
     * Check payment status
     */
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

    /**
     * Get all donations
     */
    public function getAllDonations(int $page = 1, int $perPage = 50)
    {
        return Donation::with(['user', 'weeklyDraw'])
            ->orderBy('donated_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get donations by week
     */
    public function getDonationsByWeek(int $weekId)
    {
        return Donation::with('user')
            ->where('week_id', $weekId)
            ->orderBy('donated_at', 'desc')
            ->get();
    }
}
