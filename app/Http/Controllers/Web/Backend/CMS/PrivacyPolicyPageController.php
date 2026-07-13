<?php

namespace App\Http\Controllers\Web\Backend\CMS;

use Exception;
use App\Models\CMS;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;

class PrivacyPolicyPageController extends Controller
{
    /**
     * show privacy policy page hero section data and section item
     */
    public function index(Request $request)
    {
        $data = CMS::where('page', 'privacy-policy')->where('section', 'hero')->where('name', 'item')->first();

        return view("backend.layouts.cms.tax_policy.privacy_policy", compact("data"));
    }


    /**
     * update privacy policy page hero section
     **/
    public function update(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            CMS::where('page', 'privacy-policy')
                ->where('section', 'hero')
                ->where('name', 'item')
                ->first();

            CMS::updateOrCreate(
                [
                    'page' => 'privacy-policy',
                    'section' => 'hero',
                    'name' => 'item'
                ],
                $validated_data
            );

            return back()->with('t-success', 'Content updated successfully!');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }
}

