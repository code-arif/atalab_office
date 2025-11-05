<?php

namespace App\Http\Controllers\Web\Backend\CMS\OurStory;

use Exception;
use App\Models\CMS;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;

class OurStoryPageController extends Controller
{
     /**
     * show our story page hero section data and section item
     */
    public function index(Request $request)
    {
        $data = CMS::where('page', 'our-story')->where('section', 'hero')->where('name', 'item')->first();

        return view("backend.layouts.cms.our_story.index", compact("data"));
    }


    /**
     * update our-story page hero section
     **/
    public function update(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'our-story')
                ->where('section', 'hero')
                ->where('name', 'item')
                ->first();

            // handle image
            if ($request->hasFile('image')) {
                if ($existing && $existing->image) {
                    Helper::deleteImage($existing->image);
                }

                $image_path = Helper::uploadImage($request->file('image'), 'cms/our_story');
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
