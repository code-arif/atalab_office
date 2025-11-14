<?php

namespace App\Http\Controllers\Web\Backend\CMS\Home;

use Exception;
use App\Models\CMS;
use App\Helper\Helper;
use App\Models\DrawSetting;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;

class DistributionController extends Controller
{
    /**
     * show home page distribution section data and section item
     */
    public function index(Request $request)
    {
        $data = CMS::where('page', 'home')->where('section', 'redistribution-table')->where('name', 'item')->first();

        // Get all draw settings
        $drawSettings = DrawSetting::latest()->get();

        return view("backend.layouts.cms.home.distribution", compact(["data", "drawSettings"]));
    }


    /**
     * update hero section
     **/
    public function update(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            CMS::where('page', 'home')
                ->where('section', 'redistribution-table')
                ->where('name', 'item')
                ->first();

            CMS::updateOrCreate(
                [
                    'page' => 'home',
                    'section' => 'redistribution-table',
                    'name' => 'item'
                ],
                $validated_data
            );

            return back()->with('t-success', 'Content updated successfully!');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }


    /**
     * distribution table item store
     */
    public function itemStore(Request $request)
    {
        $request->validate([
            'participants'       => 'required|integer|min:1',
            'total_pool'         => 'required|numeric|min:0',
            'recipients'         => 'required|integer|min:1',
            'odds_numerator'     => 'required|integer|min:1',
            'odds_denominator'   => 'required|integer|min:1',
            'net_per_recipient'  => 'required|numeric|min:0',
        ]);

        DrawSetting::create($request->all());

        return back()->with('success', 'Draw setting added successfully');
    }


    /**
     * Distribution table item update
     */
    public function itemUpdate(Request $request, $id)
    {
        $request->validate([
            'participants'       => 'required|integer|min:1',
            'total_pool'         => 'required|numeric|min:0',
            'recipients'         => 'required|integer|min:1',
            'odds_numerator'     => 'required|integer|min:1',
            'odds_denominator'   => 'required|integer|min:1',
            'net_per_recipient'  => 'required|numeric|min:0',
        ]);

        $drawSetting = DrawSetting::findOrFail($id);
        $drawSetting->update($request->all());

        return back()->with('success', 'Draw setting updated successfully');
    }


    /**
     * Distribution table item delete
     */
    public function itemDelete($id)
    {
        $drawSetting = DrawSetting::findOrFail($id);
        $drawSetting->delete();

        return back()->with('success', 'Draw setting deleted successfully');
    }
}
