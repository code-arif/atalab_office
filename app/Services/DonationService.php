<?php

namespace App\Services;

use Exception;
use Stripe\Stripe;
use App\Models\User;
use App\Models\Donation;
use App\Models\WeeklyDraw;
use Illuminate\Support\Str;
use Stripe\Checkout\Session;
use App\Events\DonationCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DonationService
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create standard $25 donation with DUPLICATE CHECK
     */
    public function createStandardDonation(string $successUrl, string $cancelUrl): array
    {
        return DB::transaction(function () use ($successUrl, $cancelUrl) {
            // Get current active draw
            $currentDraw = WeeklyDraw::where('status', 'active')->first();

            if (!$currentDraw) {
                throw new Exception('No active draw available. Donations are paused.');
            }

            // Check if donations are allowed (not Sunday 5 PM - Monday 12 AM)
            if (!$this->isDonationAllowed()) {
                throw new Exception('Donations are currently paused. Please try again on Monday at 12:00 AM.');
            }

            // Create Stripe checkout session
            $session = $this->stripeService->createCheckoutSession(
                25.00,
                $currentDraw,
                'standard',
                $successUrl,
                $cancelUrl
            );

            // Create unique temporary identifier
            $tempIdentifier = 'temp_' . Str::uuid();

            // Create pending donation
            $donation = Donation::create([
                'user_id' => null,
                'temp_identifier' => $tempIdentifier,
                'week_id' => $currentDraw->id,
                'amount' => 25.00,
                'stripe_payment_id' => $session->id,
                'stripe_payment_status' => 'pending',
                'is_eligible_for_draw' => false,
                'payment_type' => 'standard',
                'donated_at' => now(),
            ]);

            Log::info('Standard donation initiated', [
                'donation_id' => $donation->id,
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
     * Create custom amount donation with DUPLICATE CHECK
     */
    public function createCustomDonation(float $amount, string $successUrl, string $cancelUrl): array
    {
        return DB::transaction(function () use ($amount, $successUrl, $cancelUrl) {
            // Validate minimum amount
            if ($amount < 26) {
                throw new Exception('Custom donation must be at least $26');
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

            // Create Stripe checkout session
            $session = $this->stripeService->createCheckoutSession(
                $amount,
                $currentDraw,
                'custom',
                $successUrl,
                $cancelUrl
            );

            $tempIdentifier = 'temp_' . Str::uuid();

            // Create pending donation
            $donation = Donation::create([
                'user_id' => null,
                'temp_identifier' => $tempIdentifier,
                'week_id' => $currentDraw->id,
                'amount' => $amount,
                'stripe_payment_id' => $session->id,
                'stripe_payment_status' => 'pending',
                'is_eligible_for_draw' => false,
                'payment_type' => 'custom',
                'donated_at' => now(),
            ]);

            Log::info('Custom donation initiated', [
                'donation_id' => $donation->id,
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
        $now = \Carbon\Carbon::now('Asia/Dhaka');

        // Sunday 5 PM onwards - PAUSED
        if ($now->isSunday() && $now->hour >= 17) {
            return false;
        }

        // Monday before 12 AM - PAUSED (technically this is still Sunday night)
        if ($now->isMonday() && $now->hour < 0) {
            return false;
        }

        return true;
    }

    /**
     * Verify payment with DUPLICATE EMAIL CHECK
     */
    public function verifyPayment(string $sessionId): Donation
    {
        $session = Session::retrieve($sessionId);

        $donation = Donation::where('stripe_payment_id', $sessionId)->first();

        if (!$donation) {
            throw new Exception('Donation not found');
        }

        if ($session->payment_status === 'paid') {
            $customerEmail = $session->customer_details->email;

            // CRITICAL: Check if email already donated in this week
            $existingDonation = Donation::where('week_id', $donation->week_id)
                ->where('stripe_payment_status', 'completed')
                ->whereHas('user', function ($query) use ($customerEmail) {
                    $query->where('email', $customerEmail);
                })
                ->first();

            if ($existingDonation) {
                // Mark as duplicate and refund
                $donation->update([
                    'stripe_payment_status' => 'duplicate',
                    'is_eligible_for_draw' => false,
                ]);

                Log::warning('Duplicate donation detected', [
                    'email' => $customerEmail,
                    'week_id' => $donation->week_id
                ]);

                throw new Exception('You have already donated this week. Only one donation per week is allowed.');
            }

            // Get or create user
            $user = $this->getOrCreateUserFromStripe($session);

            $donation->update([
                'user_id' => $user->id,
                'stripe_payment_status' => 'completed',
                'stripe_charge_id' => $session->payment_intent,
                'is_eligible_for_draw' => true,
                'temp_identifier' => null,
            ]);

            // Update weekly draw stats
            $this->updateWeeklyDrawStats($donation->week_id);

            Log::info('Payment verified', [
                'donation_id' => $donation->id,
                'user_id' => $user->id,
            ]);
        }

        return $donation->fresh();
    }

    /**
     * Handle Stripe checkout completed webhook with DUPLICATE CHECK
     */
    public function handleCheckoutCompleted($session): void
    {
        $donation = Donation::where('stripe_payment_id', $session->id)->first();

        if ($donation && $donation->stripe_payment_status !== 'completed') {
            $customerEmail = $session->customer_details->email;

            // Check for duplicate donation in same week
            $existingDonation = Donation::where('week_id', $donation->week_id)
                ->where('stripe_payment_status', 'completed')
                ->whereHas('user', function ($query) use ($customerEmail) {
                    $query->where('email', $customerEmail);
                })
                ->first();

            if ($existingDonation) {
                $donation->update([
                    'stripe_payment_status' => 'duplicate',
                    'is_eligible_for_draw' => false,
                ]);

                Log::warning('Webhook: Duplicate donation blocked', [
                    'email' => $customerEmail,
                    'week_id' => $donation->week_id
                ]);

                return;
            }

            // Get or create user
            $user = $this->getOrCreateUserFromStripe($session);

            $donation->update([
                'user_id' => $user->id,
                'stripe_payment_status' => 'completed',
                'stripe_charge_id' => $session->payment_intent,
                'is_eligible_for_draw' => true,
                'temp_identifier' => null,
            ]);

            $this->updateWeeklyDrawStats($donation->week_id);

            Log::info('✅ Webhook: Checkout completed', [
                'donation_id' => $donation->id,
                'user_id' => $user->id
            ]);
        }
    }

    /**
     * Get or create user from Stripe (SECURITY: Verified data only)
     */
    protected function getOrCreateUserFromStripe($session): User
    {
        $customerDetails = $session->customer_details;

        if (!filter_var($customerDetails->email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email from Stripe');
        }

        return User::firstOrCreate(
            ['email' => $customerDetails->email],
            [
                'name' => $customerDetails->name ?? 'Anonymous Donor',
                'phone' => $customerDetails->phone ?? null,
                'email_verified_at' => now(),
                'password' => bcrypt(Str::random(16)), // Random secure password
                'role' => 'donor',
            ]
        );
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
