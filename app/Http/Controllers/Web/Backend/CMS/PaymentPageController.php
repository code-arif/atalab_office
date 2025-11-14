<?php

namespace App\Http\Controllers\Web\Backend\CMS;

use Exception;
use App\Models\CMS;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;

class PaymentPageController extends Controller
{
    /**
     * show payment page hero section data and section item
     */
    public function index(Request $request)
    {
        $data = CMS::where('page', 'payment-policy')->where('section', 'hero')->where('name', 'item')->first();

        return view("backend.layouts.cms.payment_policy.index", compact("data"));
    }


    /**
     * update payment page hero section
     **/
    public function update(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            CMS::where('page', 'payment-policy')
                ->where('section', 'hero')
                ->where('name', 'item')
                ->first();

            CMS::updateOrCreate(
                [
                    'page' => 'payment-policy',
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

