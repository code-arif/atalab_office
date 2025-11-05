<?php

namespace App\Http\Controllers\Web\Backend\CMS\HowItWorks;

use Exception;
use App\Models\CMS;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;

class HowItWorksPageController extends Controller
{
    /**
     * show how it works page hero section data and section item
     */
    public function index(Request $request)
    {
        $data = CMS::where('page', 'how-it-works')->where('section', 'hero')->where('name', 'item')->first();

        return view("backend.layouts.cms.how_it", compact("data"));
    }


    /**
     * update how it works page hero section
     **/
    public function update(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'how-it-works')
                ->where('section', 'hero')
                ->where('name', 'item')
                ->first();

            // handle image
            if ($request->hasFile('image')) {
                if ($existing && $existing->image) {
                    Helper::deleteImage($existing->image);
                }

                $image_path = Helper::uploadImage($request->file('image'), 'cms/how_it_works');
                $validated_data['image'] = $image_path;
            }

            CMS::updateOrCreate(
                [
                    'page' => 'our-story',
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
