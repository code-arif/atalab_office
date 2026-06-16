<?php

namespace App\Http\Controllers\Web\Backend\Settings;

use App\Http\Controllers\Controller;
use App\Models\StripeSetting;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class StripeSettingsController extends Controller
{

    /**
     * Display the Stripe settings page.
     */
    public function index(): View
    {
        $settings = StripeSetting::query()->first();

        return view('backend.layouts.settings.stripe_settings', compact('settings'));
    }


    /**
     * Update Stripe API credentials in the .env file.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'stripe_key'               => 'nullable|string|regex:/^[\S]*$/',
            'stripe_secret'            => 'nullable|string|regex:/^[\S]*$/',
            'stripe_webhook_secret'    => 'nullable|string|regex:/^[\S]*$/',
            'stripe_v2_webhook_secret' => 'nullable|string|regex:/^[\S]*$/',
        ]);

        try {
            $envPath    = base_path('.env');
            $envContent = File::exists($envPath) ? File::get($envPath) : '';

            $keys = [
                'STRIPE_KEY'               => $request->stripe_key,
                'STRIPE_SECRET'            => $request->stripe_secret,
                'STRIPE_WEBHOOK_SECRET'    => $request->stripe_webhook_secret,
                'STRIPE_V2_WEBHOOK_SECRET' => $request->stripe_v2_webhook_secret,
            ];

            foreach ($keys as $key => $value) {
                $value = trim((string) $value);
                if (preg_match("/^{$key}=.*$/m", $envContent)) {
                    $envContent = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $envContent);
                } else {
                    $envContent .= "\n{$key}={$value}";
                }
            }

            File::put($envPath, $envContent);

            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            return back()->with('t-success', 'Stripe credentials updated successfully.');
        } catch (Exception) {
            return back()->with('t-error', 'Failed to update Stripe credentials.');
        }
    }


    /**
     * Update platform fee, Stripe processing fees, and donation amount in the database.
     */
    public function updatePercentage(Request $request): RedirectResponse
    {
        $request->validate([
            'admin_percentage'    => 'nullable|numeric|min:0',
            'ach_flat_fee'        => 'nullable|numeric|min:0',
            'card_fee_percentage' => 'nullable|numeric|min:0',
            'card_fixed_fee'      => 'nullable|numeric|min:0',
            'donation_amount'     => 'nullable|numeric|min:0',
        ]);

        try {
            $settings = StripeSetting::query()->firstOrCreate([]);

            $settings->update([
                'admin_percentage'    => $request->admin_percentage ?? 0,
                'ach_flat_fee'        => $request->ach_flat_fee ?? 0,
                'card_fee_percentage' => $request->card_fee_percentage ?? 0,
                'card_fixed_fee'      => $request->card_fixed_fee ?? 0,
                'donation_amount'     => $request->donation_amount ?? 0,
            ]);

            return back()->with('t-success', 'Fee settings updated successfully.');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update fee settings: ' . $e->getMessage());
        }
    }
}
