<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Charge;
use Stripe\PaymentIntent;
use Exception;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createCharge(float $amount, string $token, string $description): array
    {
        try {
            $charge = Charge::create([
                'amount' => $amount * 100, // Convert to cents
                'currency' => 'usd',
                'source' => $token,
                'description' => $description,
            ]);

            return [
                'success' => true,
                'charge_id' => $charge->id,
                'payment_id' => $charge->payment_intent ?? $charge->id,
                'status' => $charge->status,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function createPaymentIntent(float $amount, array $metadata = []): array
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount * 100,
                'currency' => 'usd',
                'metadata' => $metadata,
            ]);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function refundCharge(string $chargeId, float $amount = null): array
    {
        try {
            $refund = \Stripe\Refund::create([
                'charge' => $chargeId,
                'amount' => $amount ? $amount * 100 : null,
            ]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'status' => $refund->status,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
