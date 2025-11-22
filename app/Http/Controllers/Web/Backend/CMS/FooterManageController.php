<?php

namespace App\Http\Controllers\Web\Backend\CMS;

use App\Helper\Helper;
use App\Models\Footer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class FooterManageController extends Controller
{
    // Display the footer management page
    public function index()
    {
        $data = Footer::first() ?? new Footer();
        return view('backend.layouts.cms.partials.footer', compact('data'));
    }

    // Handle footer update
    public function update(Request $request)
    {
        dd($request->all());
        $request->validate([
            'logo' => 'nullable|max:2048',
            'business_name' => 'nullable|string|max:255',
            'slogan' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'subscribe_title' => 'nullable|string|max:255',
            'subscribe_description' => 'nullable|string',
            'copyright' => 'nullable|string|max:255',

            // Social Links
            'social_links' => 'nullable|array|min:1',
            'social_links.*.platform' => 'required_with:social_links|in:linkedin,tiktok,youtube,medium,facebook,instagram,twitter,x',
            'social_links.*.url' => 'required_with:social_links|url',
            'social_links.*.icon' => 'nullable|max:1024',
        ]);

        try {
            $footer = Footer::first() ?? new Footer();

            $data = $request->only([
                'business_name',
                'slogan',
                'description',
                'subscribe_title',
                'subscribe_description',
                'copyright',
            ]);

            // Handle Logo
            if ($request->hasFile('logo')) {
                if ($footer->logo) {
                    Helper::deleteImage($footer->logo);
                }
                $data['logo'] = Helper::uploadImage($request->file('logo'), 'footer/logo');
            }

            // Handle Social Icons
            if ($request->has('social_links') && is_array($request->social_links)) {
                $socialLinks = [];
                foreach ($request->social_links as $index => $link) {
                    // Skip if platform or url is empty
                    if (empty($link['platform']) || empty($link['url'])) {
                        continue;
                    }

                    $iconPath = $link['icon'] ?? null;

                    if ($request->hasFile("social_links.$index.icon")) {
                        $iconPath = Helper::uploadImage(
                            $request->file("social_links.$index.icon"),
                            "footer/social/{$link['platform']}"
                        );
                    } elseif (isset($footer->social_links[$index]['icon'])) {
                        // Keep existing icon if no new one uploaded
                        $iconPath = $footer->social_links[$index]['icon'];
                    }

                    $socialLinks[] = [
                        'platform' => $link['platform'],
                        'url' => $link['url'],
                        'icon' => $iconPath,
                    ];
                }
                $data['social_links'] = $socialLinks;
            }

            $footer->fill($data)->save();

            return response()->json([
                'success' => true,
                'message' => 'Footer updated successfully!'
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Footer Update Failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update footer. Please try again.'
            ], 500);
        }
    }
}
