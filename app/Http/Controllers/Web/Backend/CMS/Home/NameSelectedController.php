<?php

namespace App\Http\Controllers\Web\Backend\CMS\Home;

use Exception;
use App\Models\CMS;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;

class NameSelectedController extends Controller
{
    /**
     * show home page selected_name section data and section item
     */
    public function index(Request $request)
    {
        $data = CMS::where('page', 'home')->where('section', 'selected_name')->where('name', 'item')->first();

        return view("backend.layouts.cms.home.selected_name", compact("data"));
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
                ->where('section', 'selected_name')
                ->where('name', 'item')
                ->first();

            // handle image
            if ($request->hasFile('image')) {
                if ($existing && $existing->image) {
                    Helper::deleteImage($existing->image);
                }

                $image_path = Helper::uploadImage($request->file('image'), 'cms/home/selected_name');
                $validated_data['image'] = $image_path;
            }

            CMS::updateOrCreate(
                [
                    'page' => 'home',
                    'section' => 'selected_name',
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
