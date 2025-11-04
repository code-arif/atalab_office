<?php

namespace App\Http\Controllers\Web\Backend\CMS\Home;

use Exception;
use App\Models\CMS;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;

class HeroController extends Controller
{
    /**
     * show home page hero section data and section item
     */
    public function index(Request $request)
    {
        $data = CMS::where('page', 'home')->where('section', 'hero')->where('name', 'item')->first();

        return view("backend.layouts.cms.home.hero", compact("data"));
    }


    /**
     * update hero section
     **/
    public function update(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'home')
                ->where('section', 'hero')
                ->where('name', 'item')
                ->first();

            // handle image
            if ($request->hasFile('image')) {
                if ($existing && $existing->image) {
                    Helper::deleteImage($existing->image);
                }

                $image_path = Helper::uploadImage($request->file('image'), 'cms/home/hero');
                $validated_data['image'] = $image_path;
            }

            CMS::updateOrCreate(
                [
                    'page' => 'home',
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
