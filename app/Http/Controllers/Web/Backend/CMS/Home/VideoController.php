<?php

namespace App\Http\Controllers\Web\Backend\CMS\Home;

use Exception;
use App\Models\CMS;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;

class VideoController extends Controller
{
    /**
     * show home page video section data and section item
     */
    public function index(Request $request)
    {
        $data = CMS::where('page', 'home')->where('section', 'video')->where('name', 'item')->first();

        return view("backend.layouts.cms.home.video_section", compact("data"));
    }


    /**
     * update selected_name section
     **/
    public function update(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'home')
                ->where('section', 'video')
                ->where('name', 'item')
                ->first();

            // handle image
            if ($request->hasFile('video_path')) {
                if ($existing && $existing->video_path) {
                    Helper::deleteImage($existing->video_path);
                }

                $image_path = Helper::uploadImage($request->file('video_path'), 'cms/home/video_path');
                $validated_data['video_path'] = $image_path;
            }

            CMS::updateOrCreate(
                [
                    'page' => 'home',
                    'section' => 'video',
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
