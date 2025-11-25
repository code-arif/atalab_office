<?php

namespace App\Services;

use Exception;
use Stripe\Stripe;
use App\Models\Donation;
use App\Models\WeeklyDraw;
use Stripe\Checkout\Session;
use App\Events\DonationCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     * Create standard $25 donation (WITH SESSION VALIDATION)
     */
    public function createStandardDonation(string $sessionToken, string $successUrl, string $cancelUrl): array
    {
        return DB::transaction(function () use ($sessionToken, $successUrl, $cancelUrl) {
            // Validate session token
            $user = $this->registrationService->validateSession($sessionToken);

            if (!$user) {
                throw new Exception('Invalid or expired session. Please register again.');
            }

            // Get current active draw
            $currentDraw = WeeklyDraw::where('status', 'active')->first();

            if (!$currentDraw) {
                throw new Exception('No active draw available. Donations are paused.');
            }

            // Check if donations are allowed (not Sunday 5 PM - Monday 12 AM)
            if (!$this->isDonationAllowed()) {
                throw new Exception('Donations are currently paused. Please try again on Monday at 12:00 AM.');
            }

            // CRITICAL: Check if user already donated this week
            $existingDonation = Donation::where('user_id', $user->id)
                ->where('week_id', $currentDraw->id)
                ->where('stripe_payment_status', 'completed')
                ->first();

            if ($existingDonation) {
                throw new Exception('You have already donated this week. Only one donation per week is allowed.');
            }

            // Check for pending donation
            $pendingDonation = Donation::where('user_id', $user->id)
                ->where('week_id', $currentDraw->id)
                ->where('stripe_payment_status', 'pending')
                ->first();

            if ($pendingDonation) {
                throw new Exception('You already have a pending donation. Please complete or cancel it first.');
            }

            // Create Stripe checkout session
            $session = $this->stripeService->createCheckoutSession(
                25.00,
                $currentDraw,
                'standard',
                $successUrl,
                $cancelUrl,
                $user // Pass user for pre-filled checkout
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
            ]);

            Log::info('Standard donation initiated', [
                'donation_id' => $donation->id,
                'user_id' => $user->id,
                'donor_id' => $user->donor_id,
                'session_id' => $session->id,
            ]);

            event(new DonationCreated($donation));

            return [
                'checkout_url' => $session->url,
                'session_id' => $session->id,
                'donation_id' => $donation->id
            ];
        });
    }

    /**
     * Create custom amount donation (WITH SESSION VALIDATION)
     */
    public function createCustomDonation(string $sessionToken, float $amount, string $successUrl, string $cancelUrl): array
    {
        return DB::transaction(function () use ($sessionToken, $amount, $successUrl, $cancelUrl) {
            // Validate minimum amount
            if ($amount < 26) {
                throw new Exception('Custom donation must be at least $26');
            }

            // Validate session token
            $user = $this->registrationService->validateSession($sessionToken);

            if (!$user) {
                throw new Exception('Invalid or expired session. Please register again.');
            }

            // Get current active draw
            $currentDraw = WeeklyDraw::where('status', 'active')->first();

            if (!$currentDraw) {
                throw new Exception('No active draw available. Donations are paused.');
            }

            // Check if donations are allowed
            if (!$this->isDonationAllowed()) {
                throw new Exception('Donations are currently paused. Please try again on Monday at 12:00 AM.');
            }

            // CRITICAL: Check if user already donated this week
            $existingDonation = Donation::where('user_id', $user->id)
                ->where('week_id', $currentDraw->id)
                ->where('stripe_payment_status', 'completed')
                ->first();

            if ($existingDonation) {
                throw new Exception('You have already donated this week. Only one donation per week is allowed.');
            }

            // Create Stripe checkout session
            $session = $this->stripeService->createCheckoutSession(
                $amount,
                $currentDraw,
                'custom',
                $successUrl,
                $cancelUrl,
                $user
            );

            // Create donation record
            $donation = Donation::create([
                'user_id' => $user->id,
                'week_id' => $currentDraw->id,
                'amount' => $amount,
                'stripe_payment_id' => $session->id,
                'stripe_payment_status' => 'pending',
                'is_eligible_for_draw' => false,
                'payment_type' => 'custom',
                'donated_at' => now(config('app.timezone')),
            ]);

            Log::info('Custom donation initiated', [
                'donation_id' => $donation->id,
                'user_id' => $user->id,
                'donor_id' => $user->donor_id,
                'amount' => $amount,
            ]);

            return [
                'checkout_url' => $session->url,
                'session_id' => $session->id,
                'donation_id' => $donation->id
            ];
        });
    }

    /**
     * Check if donation is allowed based on time window
     */
    protected function isDonationAllowed(): bool
    {
        $now = now(config('app.timezone'));

        // Sunday 5 PM onwards - PAUSED
        if ($now->isSunday() && $now->hour >= 17) {
            return false;
        }

        // Monday before 12 AM - PAUSED
        if ($now->isMonday() && $now->hour < 0) {
            return false;
        }

        return true;
    }

    /**
     * Verify payment after Stripe redirect
     */
    public function verifyPayment(string $sessionId): Donation
    {
        $session = Session::retrieve($sessionId);

        $donation = Donation::where('stripe_payment_id', $sessionId)->first();

        if (!$donation) {
            throw new Exception('Donation not found');
        }

        if ($session->payment_status === 'paid' && $donation->stripe_payment_status !== 'completed') {
            $donation->update([
                'stripe_payment_status' => 'completed',
                'stripe_charge_id' => $session->payment_intent,
                'is_eligible_for_draw' => true,
            ]);

            // Update weekly draw stats
            $this->updateWeeklyDrawStats($donation->week_id);

            // Mark session as donated
            $this->registrationService->markSessionAsDonated($session->metadata->session_token ?? '');

            Log::info('Payment verified', [
                'donation_id' => $donation->id,
                'user_id' => $donation->user_id,
            ]);
        }

        return $donation->fresh();
    }

    /**
     * Handle Stripe checkout completed webhook
     */
    public function handleCheckoutCompleted($session): void
    {
        $donation = Donation::where('stripe_payment_id', $session->id)->first();

        if ($donation && $donation->stripe_payment_status !== 'completed') {
            $donation->update([
                'stripe_payment_status' => 'completed',
                'stripe_charge_id' => $session->payment_intent,
                'is_eligible_for_draw' => true,
            ]);

            $this->updateWeeklyDrawStats($donation->week_id);

            Log::info('✅ Webhook: Checkout completed', [
                'donation_id' => $donation->id,
                'user_id' => $donation->user_id
            ]);
        }
    }

    /**
     * Update weekly draw statistics
     */
    protected function updateWeeklyDrawStats(int $weekId): void
    {
        $draw = WeeklyDraw::find($weekId);

        if ($draw) {
            $stats = Donation::where('week_id', $weekId)
                ->where('stripe_payment_status', 'completed')
                ->selectRaw('SUM(amount) as total, COUNT(DISTINCT user_id) as participants')
                ->first();

            $draw->update([
                'total_pool' => $stats->total ?? 0,
                'total_participants' => $stats->participants ?? 0,
            ]);
        }
    }

    /**
     * Handle payment succeeded webhook
     */
    public function handlePaymentSucceeded($paymentIntent): void
    {
        $donation = Donation::where('stripe_charge_id', $paymentIntent->id)->first();

        if ($donation) {
            $donation->update([
                'stripe_payment_status' => 'completed',
                'is_eligible_for_draw' => true,
            ]);

            $this->updateWeeklyDrawStats($donation->week_id);
        }
    }

    /**
     * Handle payment failed webhook
     */
    public function handlePaymentFailed($paymentIntent): void
    {
        $donation = Donation::where('stripe_charge_id', $paymentIntent->id)->first();

        if ($donation) {
            $donation->update([
                'stripe_payment_status' => 'failed',
                'is_eligible_for_draw' => false,
            ]);
        }
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
     * Get all donations (Admin)
     */
    public function getAllDonations(int $page = 1, int $perPage = 50)
    {
        return Donation::with(['user', 'weeklyDraw'])
            ->orderBy('donated_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get donations by week (Admin)
     */
    public function getDonationsByWeek(int $weekId)
    {
        return Donation::with('user')
            ->where('week_id', $weekId)
            ->orderBy('donated_at', 'desc')
            ->get();
    }
}
