<?php

namespace App\Http\Controllers\Web\Backend\CMS;

use Exception;
use App\Models\CMS;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;

class TopBarManageController extends Controller
{
    /**
     * show top bar section data and section item
     */

    public function index(Request $request)
    {
        $data = CMS::where('page', 'partials')->where('section', 'topbar')->where('name', 'topbar')->first();

        return view("backend.layouts.cms.partials.topbar", compact("data"));
    }


    /**
     * update top bar section
     **/
    public function update(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            CMS::where('page', 'partials')
                ->where('section', 'topbar')
                ->where('name', 'topbar')
                ->first();

            CMS::updateOrCreate(
                [
                    'page' => 'partials',
                    'section' => 'topbar',
                    'name' => 'topbar'
                ],
                $validated_data
            );

            return back()->with('t-success', 'Content updated successfully!');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }
}
