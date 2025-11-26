<?php

namespace App\Services;

use Exception;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    protected $client;
    protected $fromNumber;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
        $this->fromNumber = config('services.twilio.from');
    }

    /**
     * Send OTP via SMS
     */
    public function sendOTP(string $phone, string $otp): bool
    {
        try {
            $message = "Your verification code is: {$otp}\n\nThis code will expire in 10 minutes.\n\n" . config('app.name');

            $this->client->messages->create($phone, [
                'from' => $this->fromNumber,
                'body' => $message
            ]);

            Log::info('SMS sent successfully', [
                'phone' => $phone,
                'otp' => $otp
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send SMS', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
