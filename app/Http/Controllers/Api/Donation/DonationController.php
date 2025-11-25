<?php

namespace App\Http\Controllers\Api\Donation;

use Stripe\Webhook;
use Illuminate\Http\Request;
use App\Services\StripeService;
use App\Services\DonationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DonationController extends Controller
{
    protected $donationService;
    protected $stripeService;

    public function __construct(DonationService $donationService, StripeService $stripeService)
    {
        $this->donationService = $donationService;
        $this->stripeService = $stripeService;
    }

    /**
     * Create standard $25 donation (REQUIRES SESSION TOKEN)
     */
    public function createStandardDonation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $baseUrl = config('app.frontend_url', 'http://localhost:5173');
            $successUrl = $baseUrl . '/success?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = $baseUrl . '/donation/cancel';

            $result = $this->donationService->createStandardDonation(
                $request->session_token,
                $successUrl,
                $cancelUrl
            );

            return response()->json([
                'success' => true,
                'checkout_url' => $result['checkout_url'],
                'session_id' => $result['session_id']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Create custom amount donation (REQUIRES SESSION TOKEN)
     */
    public function createCustomDonation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
            'amount' => 'required|numeric|min:26',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $baseUrl = config('app.frontend_url', 'http://localhost:5173');
            $successUrl = $baseUrl . '/success?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = $baseUrl . '/donation/cancel';

            $result = $this->donationService->createCustomDonation(
                $request->session_token,
                $request->amount,
                $successUrl,
                $cancelUrl
            );

            return response()->json([
                'success' => true,
                'checkout_url' => $result['checkout_url'],
                'session_id' => $result['session_id']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Verify payment after Stripe redirect
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $donation = $this->donationService->verifyPayment($request->session_id);

            return response()->json([
                'success' => true,
                'donation' => $donation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(string $paymentId): JsonResponse
    {
        try {
            $status = $this->donationService->checkPaymentStatus($paymentId);

            return response()->json([
                'success' => true,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Handle Stripe Webhook (MOST IMPORTANT FOR PAYMENT COMPLETION)
     */
    public function handleStripeWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        // Log webhook received
        Log::info('Webhook received', [
            'payload' => $payload,
            'signature' => $sigHeader
        ]);

        try {
            // Verify webhook signature
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);

            Log::info('Webhook event type: ' . $event->type);

            // Handle different event types
            switch ($event->type) {
                case 'checkout.session.completed':
                    $this->donationService->handleCheckoutCompleted($event->data->object);
                    Log::info('Checkout completed webhook processed');
                    break;

                case 'payment_intent.succeeded':
                    $this->donationService->handlePaymentSucceeded($event->data->object);
                    Log::info('Payment succeeded webhook processed');
                    break;

                case 'payment_intent.payment_failed':
                    $this->donationService->handlePaymentFailed($event->data->object);
                    Log::info('Payment failed webhook processed');
                    break;

                default:
                    Log::info('Unhandled webhook event: ' . $event->type);
            }

            return response()->json(['success' => true], 200);
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            Log::error('Invalid webhook payload', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            Log::error('Invalid webhook signature', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all donations (Admin)
     */
    public function getAllDonations(Request $request): JsonResponse
    {
        try {
            $donations = $this->donationService->getAllDonations(
                $request->input('page', 1),
                $request->input('per_page', 50)
            );

            return response()->json([
                'success' => true,
                'donations' => $donations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get donations by week (Admin)
     */
    public function getDonationsByWeek(int $weekId): JsonResponse
    {
        try {
            $donations = $this->donationService->getDonationsByWeek($weekId);

            return response()->json([
                'success' => true,
                'donations' => $donations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
