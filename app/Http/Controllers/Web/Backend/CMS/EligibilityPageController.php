<?php

namespace App\Http\Controllers\Web\Backend\CMS;

use Exception;
use App\Models\CMS;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;

class EligibilityPageController extends Controller
{
    /**
     * show eligibility page hero section data and section item
     */
    public function index(Request $request)
    {
        $data = CMS::where('page', 'eligibility')->where('section', 'hero')->where('name', 'item')->first();

        return view("backend.layouts.cms.eligibility.index", compact("data"));
    }


    /**
     * update eligibility page hero section
     **/
    public function update(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            CMS::where('page', 'eligibility')
                ->where('section', 'hero')
                ->where('name', 'item')
                ->first();

            CMS::updateOrCreate(
                [
                    'page' => 'eligibility',
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
