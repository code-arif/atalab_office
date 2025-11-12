<?php

namespace App\Http\Controllers\Api\Donation;

use Stripe\Webhook;
use App\Helper\Helper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use App\Services\StripeService;
use App\Services\DonationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
     * Create standard $25 donation
     * No user data required - collected in Stripe checkout
     */
    public function createStandardDonation(Request $request): JsonResponse
    {
        try {
            // Generate success/cancel URLs automatically
            $baseUrl = 'https://atalab1115_laravel.test';
            $successUrl = $baseUrl . '/donation/success?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = $baseUrl . '/donation/cancel';

            $result = $this->donationService->createStandardDonation(
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
            ], 500);
        }
    }

    /**
     * Create custom amount donation
     * Only amount required - user data collected in Stripe checkout
     */
    public function createCustomDonation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:26',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Generate success/cancel URLs automatically
            $baseUrl = config('app.frontend_url') ?: config('app.url');
            $successUrl = $baseUrl . '/donation/success?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = $baseUrl . '/donation/cancel';

            $result = $this->donationService->createCustomDonation(
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
            ], 500);
        }
    }

    /**
     * Verify payment after Stripe redirect for locl host.
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
     * Handle Stripe Webhook
     */
    public function handleStripeWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);

            switch ($event->type) {
                case 'checkout.session.completed':
                    $this->donationService->handleCheckoutCompleted($event->data->object);
                    break;

                case 'payment_intent.succeeded':
                    $this->donationService->handlePaymentSucceeded($event->data->object);
                    break;

                case 'payment_intent.payment_failed':
                    $this->donationService->handlePaymentFailed($event->data->object);
                    break;
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
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

    /**
     * checkout
     */
    public function checkout(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            // return Helper::(false, 'Validation failed', 422, $validator->errors());
            return 'hello';
        }

        try {

            $data = $validator->validated();
            $uid = Str::uuid();

            $successUrl = route('payment.stripe.success') . '?token={CHECKOUT_SESSION_ID}';
            $cancelUrl = route('payment.stripe.cancel') . '?token={CHECKOUT_SESSION_ID}';

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'donation'
                        ],
                        'unit_amount' => $data['price'] * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'metadata' => [
                    'order_id' => $uid,
                    // 'user_id' => auth('api')->user()->id
                ],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);

            $data = [
                'checkout_url' => $session->url
            ];

            // return Helper::jsonResponse(true, 'Checkout session created successfully', 200, $data);
            // return 1;
            return response()->json($data);
        } catch (ModelNotFoundException $e) {

            Log::error($e->getMessage());
            // return redirect()->to($this->redirectFail);
        } catch (ApiErrorException $e) {

            Log::error($e->getMessage());
            // return redirect()->to($this->redirectFail);
        }
    }

    public function success(Request $request)
    {
        $validatedData = $request->validate([
            'token' => ['required', 'string']
        ]);

        try {

            $session = Session::retrieve($validatedData['token']);
            if ($session->payment_status === 'paid') {

                Transaction::create([
                    'user_id'   => $session->metadata['user_id'],
                    'amount'    => $session->amount_total / 100,
                    'currency'  => $session->currency,
                    'trx_id'    => $session->id,
                    'type'      => 'increment',
                    'status'    => 'success',
                    'metadata'  => json_encode($session->metadata)
                ]);

                return redirect()->to($this->redirectSuccess);
            }

            if ($session->payment_status === 'unpaid' || $session->payment_status === 'no_payment_required') {
                return redirect()->to($this->redirectFail);
            }

            return redirect()->to($this->redirectFail);
        } catch (ApiErrorException $e) {

            Log::error($e->getMessage());
            return redirect()->to($this->redirectFail);
        } catch (ModelNotFoundException $e) {

            Log::error($e->getMessage());
            return redirect()->to($this->redirectFail);
        }
    }
    public function failure(Request $request)
    {
        // return redirect()->to($this->redirectFail);
        return 0;
    }
}
