<?php

namespace App\Http\Controllers\Api\Auth;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\RegistrationService;
use Illuminate\Support\Facades\Validator;

class RegistrationController extends Controller
{
    protected $registrationService;

    public function __construct(RegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    /**
     * Register new user (sends OTP, stores in cache)
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => [
                'required',
                'string',
                'regex:/^\+1\d{10}$/',
            ],
            'address' => 'required|string|max:500',
        ], [
            'phone.regex' => 'Please enter a valid phone number (e.g., +12345678901)',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->registrationService->registerUser($request->all());

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
