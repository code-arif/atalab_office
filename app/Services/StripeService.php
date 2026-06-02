<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Customer;
use App\Models\WeeklyDraw;
use App\Models\User;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\Log;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create Stripe checkout session with pre-filled customer data
     * Uses Stripe Customer object for better data persistence
     */
    public function createCheckoutSession(
        float $totalAmount,
        WeeklyDraw $draw,
        string $type,
        string $successUrl,
        string $cancelUrl,
        ?User $user = null,
        string $paymentMethodType = 'card',
        float $baseAmount = 0.00,
        float $processingFee = 0.00
    ): Session {

        $metadata = [
            'week_id' => $draw->id,
            'week_number' => $draw->week_number,
            'payment_type' => $type,
            'user_id' => $user ? $user->id : null,
            'payment_method_type' => $paymentMethodType,
            'base_amount' => $baseAmount,
            'processing_fee' => $processingFee,
            'total_amount' => $totalAmount,
        ];

        $paymentMethodTypes = ['card'];
        if ($paymentMethodType === 'us_bank_account') {
            $paymentMethodTypes = ['us_bank_account'];
        }

        // Base session data
        $sessionData = [
            'payment_method_types' => $paymentMethodTypes,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $type === 'standard'
                            ? 'Standard Donation - $' . number_format($baseAmount, 2)
                            : "Custom Donation - $" . number_format($baseAmount, 2),
                        'description' => "Weekly Draw #{$draw->week_number}" . ($processingFee > 0 ? " (includes $" . number_format($processingFee, 2) . " fee)" : ""),
                    ],
                    'unit_amount' => (int) round($totalAmount * 100), // Convert to cents
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => $metadata,
        ];

        // If user exists, create/retrieve Stripe Customer and pre-fill data
        if ($user) {
            try {
                // Get or create Stripe customer
                $stripeCustomer = $this->getOrCreateStripeCustomer($user);

                // Use existing customer
                $sessionData['customer'] = $stripeCustomer->id;

                // This will pre-fill email and payment details
                $sessionData['customer_update'] = [
                    'name' => 'auto',      // Update name from payment form
                    'address' => 'auto',   // Update address from payment form
                ];

                Log::info('Using Stripe customer', [
                    'customer_id' => $stripeCustomer->id,
                    'user_id' => $user->id,
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to create Stripe customer, falling back to guest checkout', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                ]);

                // Fallback: Use email pre-fill only
                $sessionData['customer_email'] = $user->email;
            }
        }

        try {
            $session = Session::create($sessionData);

            Log::info('Stripe checkout session created', [
                'session_id' => $session->id,
                'amount' => $totalAmount,
                'user_id' => $user?->id,
            ]);

            return $session;
        } catch (\Exception $e) {
            Log::error('Stripe session creation failed', [
                'error' => $e->getMessage(),
                'user_id' => $user?->id,
            ]);
            throw $e;
        }
    }

    /**
     * Get or create Stripe Customer with pre-filled data
     * This allows Stripe to remember user details
     */
    protected function getOrCreateStripeCustomer(User $user): Customer
    {
        // Check if user already has a Stripe customer ID
        if ($user->stripe_customer_id) {
            try {
                return Customer::retrieve($user->stripe_customer_id);
            } catch (\Exception $e) {
                Log::warning('Stripe customer not found, creating new', [
                    'old_customer_id' => $user->stripe_customer_id,
                    'user_id' => $user->id,
                ]);
            }
        }

        // Create new Stripe customer with all details
        $customerData = [
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => [
                'user_id' => $user->id,
                'donor_id' => $user->donor_id ?? 'pending',
            ],
        ];

        // Add phone if available
        if ($user->phone) {
            $customerData['phone'] = $this->formatPhoneForStripe($user->phone);
        }

        // Add address if available
        if ($user->address) {
            $customerData['address'] = [
                'line1' => $user->address,
                'country' => 'US', // Change based on your needs
            ];
        }

        try {
            $customer = Customer::create($customerData);

            // Save Stripe customer ID to user
            $user->update(['stripe_customer_id' => $customer->id]);

            Log::info('Stripe customer created', [
                'customer_id' => $customer->id,
                'user_id' => $user->id,
            ]);

            return $customer;
        } catch (\Exception $e) {
            Log::error('Failed to create Stripe customer', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            throw $e;
        }
    }

    /**
     * Format phone number for Stripe (E.164 format)
     * Example: +12345678901
     */
    protected function formatPhoneForStripe(string $phone): string
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // If already has +, return as is
        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        // Add + if missing
        return '+' . ltrim($phone, '0');
    }

    /**
     * Update Stripe customer details after payment
     * (Called from webhook after successful payment)
     */
    public function updateCustomerFromCheckout(string $customerId, array $details): void
    {
        try {
            Customer::update($customerId, [
                'name' => $details['name'] ?? null,
                'phone' => $details['phone'] ?? null,
                'address' => $details['address'] ?? null,
            ]);

            Log::info('Stripe customer updated from checkout', [
                'customer_id' => $customerId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update Stripe customer', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
