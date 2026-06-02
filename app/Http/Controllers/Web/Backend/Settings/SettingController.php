<?php

namespace App\Http\Controllers\Web\Backend\Settings;


use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Display the system settings page.
     */
    public function index(): View
    {
        $setting = Setting::latest('id')->first();
        return view('backend.layouts.settings.general_settings', compact('setting'));
    }

    /**
     * Update the system settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name'           => 'nullable',
            'title'          => 'nullable',
            'description'    => 'nullable',
            'phone'          => 'nullable',
            'email'          => 'nullable',
            'copyright'      => 'nullable',
            'keywords'       => 'nullable',
            'author'         => 'nullable',
            'address'        => 'nullable',
            'logo'           => 'nullable',
            'favicon'        => 'nullable',
        ]);

        try {
            $setting = Setting::first();
            if ($request->hasFile('logo')) {
                if ($setting && $setting->logo && file_exists(public_path($setting->logo))) {
                    Helper::deleteImage(public_path($setting->logo));
                }
                // $validatedData['logo'] = Helper::uploadImage($request->file('logo'), 'settings', time() . '_' . Helper::getFileName($request->file('logo')));
                $validatedData['logo']  = Helper::uploadImage($request->logo, 'settings');
            }
            if ($request->hasFile('favicon')) {
                if ($setting && $setting->favicon && file_exists(public_path($setting->favicon))) {
                    Helper::deleteImage(public_path($setting->favicon));
                }
                // $validatedData['favicon'] = Helper::uploadImage($request->file('favicon'), 'settings', time() . '_' . Helper::getFileName($request->file('favicon')));
                $validatedData['favicon']  = Helper::uploadImage($request->favicon, 'settings');
            }

            Setting::updateOrCreate(
                [
                    'id' => 1
                ],
                $validatedData
            );
            return back()->with('t-success', 'Updated successfully');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update' . $e->getMessage());
        }
    }

    public function updateAdminPercentage(Request $request): RedirectResponse
    {
        $request->validate([
            'admin_percentage'    => 'nullable|string|regex:/^\S+$/',
        ]);

        try {
            $envPath = base_path('.env');
            $envContent = File::exists($envPath) ? File::get($envPath) : '';

            $keys = [
                'CAMP_EXTRA_PRICE'    => $request->admin_percentage,
            ];

            foreach ($keys as $key => $value) {
                $value = trim($value); // remove extra spaces
                if (preg_match("/^{$key}=.*$/m", $envContent)) {
                    $envContent = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $envContent);
                } else {
                    $envContent .= "\n{$key}={$value}";
                }
            }

            File::put($envPath, $envContent);

            // Clear caches
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');

            return back()->with('t-success', 'Updated Admin & Seller Profit');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }
}
