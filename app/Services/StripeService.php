<?php

namespace App\Services;

use App\Models\User;
use App\Models\WeeklyDraw;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Transfer;
use Stripe\Payout;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create Stripe checkout session
     * Collects user information in Stripe checkout form
     */
    public function createCheckoutSession(
        float $amount,
        WeeklyDraw $draw,
        string $paymentType,
        string $successUrl,
        string $cancelUrl
    ): Session {
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => (int)($amount * 100), // Convert to cents
                    'product_data' => [
                        'name' => $paymentType === 'standard'
                            ? 'Weekly Draw Donation - $25'
                            : 'Custom Weekly Draw Donation',
                        'description' => "Donation for Week #{$draw->week_number}",
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            // Stripe will collect customer info
            'billing_address_collection' => 'auto',
            'phone_number_collection' => [
                'enabled' => true,
            ],
            'metadata' => [
                'week_id' => $draw->id,
                'payment_type' => $paymentType,
                'week_number' => $draw->week_number,
            ],
        ]);

        return $session;
    }

    /**
     * Process payout to winner
     * Note: This requires Stripe Connect or manual bank transfer
     */
    public function processPayoutToWinner(string $recipientEmail, float $amount, string $description): array
    {
        try {
            // For real implementation, you would need:
            // 1. Stripe Connect account for each winner
            // 2. Or collect bank details and use Stripe Payouts API
            // 3. Or use a third-party service like PayPal

            // This is a placeholder - implement based on your payout method

            // Example with Stripe Connect Transfer:
            // $transfer = Transfer::create([
            //     'amount' => (int)($amount * 100),
            //     'currency' => 'usd',
            //     'destination' => $stripeConnectAccountId,
            //     'description' => $description,
            // ]);

            return [
                'success' => true,
                'payout_id' => 'po_' . uniqid(),
                'amount' => $amount,
                'status' => 'processing'
            ];
        } catch (\Exception $e) {
            throw new \Exception('Payout failed: ' . $e->getMessage());
        }
    }

    /**
     * Refund a payment
     */
    public function refundPayment(string $chargeId, float $amount = null): array
    {
        try {
            $refundData = [
                'charge' => $chargeId,
            ];

            if ($amount) {
                $refundData['amount'] = (int)($amount * 100);
            }

            $refund = \Stripe\Refund::create($refundData);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'status' => $refund->status,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Refund failed: ' . $e->getMessage());
        }
    }

    /**
     * Get payment details
     */
    public function getPaymentDetails(string $paymentIntentId): array
    {
        try {
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

            return [
                'id' => $paymentIntent->id,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
                'status' => $paymentIntent->status,
                'created' => $paymentIntent->created,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Failed to retrieve payment details: ' . $e->getMessage());
        }
    }
}
