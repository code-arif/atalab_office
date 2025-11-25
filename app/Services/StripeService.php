<?php

namespace App\Services;

use Stripe\Stripe;
use App\Models\WeeklyDraw;
use App\Models\User;
use Stripe\Checkout\Session;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create Stripe checkout session with pre-filled customer data
     */
    public function createCheckoutSession(
        float $amount,
        WeeklyDraw $draw,
        string $type,
        string $successUrl,
        string $cancelUrl,
        ?User $user = null
    ): Session {

        $metadata = [
            'week_id' => $draw->id,
            'week_number' => $draw->week_number,
            'payment_type' => $type,
        ];

        // Pre-fill customer information if user exists
        $sessionData = [
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $type === 'standard'
                            ? 'Standard Donation - $25'
                            : "Custom Donation - $" . number_format($amount, 2),
                        'description' => "Weekly Draw #{$draw->week_number}",
                    ],
                    'unit_amount' => $amount * 100, // Convert to cents
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => $metadata,
        ];

        // Pre-fill customer data if user exists
        if ($user) {
            $sessionData['customer_email'] = $user->email;
            $sessionData['customer_creation'] = 'always';

            // Add phone if available (Stripe format: E.164)
            if ($user->phone) {
                $sessionData['phone_number_collection'] = ['enabled' => true];
            }
        }

        return Session::create($sessionData);
    }
}
