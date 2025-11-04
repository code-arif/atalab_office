<?php

namespace App\Http\Controllers\Web\Backend\CMS\Home;

use Exception;
use App\Models\CMS;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;

class QuoteController extends Controller
{
    /**
     * show home page quote section data and section item
     */
    public function index(Request $request)
    {
        $data = CMS::where('page', 'home')->where('section', 'quote')->where('name', 'item')->first();

        return view("backend.layouts.cms.home.quote", compact("data"));
    }


    /**
     * update quote section
     **/
    public function update(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            CMS::where('page', 'home')
                ->where('section', 'quote')
                ->where('name', 'item')
                ->first();

            CMS::updateOrCreate(
                [
                    'page' => 'home',
                    'section' => 'quote',
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
