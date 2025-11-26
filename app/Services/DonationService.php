<?php

namespace App\Services;

use Exception;
use Stripe\Stripe;
use App\Models\User;
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
     * Create standard $25 donation (USER ID BASED)
     */
    public function createStandardDonation(int $userId, string $successUrl, string $cancelUrl): array
    {
        return DB::transaction(function () use ($userId, $successUrl, $cancelUrl) {
            // Validate user
            $user = User::find($userId);

            if (!$user || !$user->email_verified_at || !$user->phone_verified_at) {
                throw new Exception('User not verified. Please complete registration first.');
            }

            // Get current active draw
            $currentDraw = WeeklyDraw::where('status', 'active')->first();

            if (!$currentDraw) {
                throw new Exception('No active draw available. Donations are paused.');
            }

            // Check donation time window
            if (!$this->isDonationAllowed()) {
                throw new Exception('Donations are currently paused. Please try again on Monday at 12:00 AM.');
            }

            // Check if user can donate this week
            if (!$this->registrationService->canUserDonate($userId, $currentDraw->id)) {
                throw new Exception('You have already donated this week. Only one donation per week is allowed.');
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
            ]);

            Log::info('Standard donation initiated', [
                'donation_id' => $donation->id,
                'user_id' => $user->id,
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
     * Create custom amount donation (USER ID BASED)
     */
    public function createCustomDonation(int $userId, float $amount, string $successUrl, string $cancelUrl): array
    {
        return DB::transaction(function () use ($userId, $amount, $successUrl, $cancelUrl) {
            if ($amount < 26) {
                throw new Exception('Custom donation must be at least $26');
            }

            $user = User::find($userId);

            if (!$user || !$user->email_verified_at || !$user->phone_verified_at) {
                throw new Exception('User not verified. Please complete registration first.');
            }

            $currentDraw = WeeklyDraw::where('status', 'active')->first();

            if (!$currentDraw) {
                throw new Exception('No active draw available. Donations are paused.');
            }

            if (!$this->isDonationAllowed()) {
                throw new Exception('Donations are currently paused. Please try again on Monday at 12:00 AM.');
            }

            if (!$this->registrationService->canUserDonate($userId, $currentDraw->id)) {
                throw new Exception('You have already donated this week. Only one donation per week is allowed.');
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
            ]);

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

            // IMPORTANT: Assign donor_id on first donation
            $this->registrationService->assignDonorId($donation->user_id);

            // Update weekly draw stats
            $this->updateWeeklyDrawStats($donation->week_id);

            Log::info('Payment verified and donor_id assigned', [
                'donation_id' => $donation->id,
                'user_id' => $donation->user_id,
            ]);
        }

        return $donation->fresh()->load('user');
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

            // Assign donor_id
            $this->registrationService->assignDonorId($donation->user_id);

            $this->updateWeeklyDrawStats($donation->week_id);

            Log::info('Checkout completed', [
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
