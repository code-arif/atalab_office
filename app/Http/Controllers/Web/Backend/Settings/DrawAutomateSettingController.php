<?php

namespace App\Http\Controllers\Web\Backend\Settings;

use App\Http\Controllers\Controller;
use App\Models\DrawAutomateSetting;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DrawAutomateSettingController extends Controller
{
    /**
     * Display the Draw Automation settings page.
     */
    public function index(): View
    {
        $settings = DrawAutomateSetting::firstOrCreate([], [
            'admin_fee_percentage' => 7.50,
            'odds_ratio' => 400,
            'minimum_participants' => 100,
            'winner_exclusion_months' => 6,
            'draw_start_day' => 'Monday',
            'draw_start_time' => '00:00:00',
            'draw_end_day' => 'Sunday',
            'draw_end_time' => '17:00:00',
        ]);

        return view('backend.layouts.settings.draw_automate_settings', compact('settings'));
    }

    /**
     * Update the Draw Automation settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'admin_fee_percentage' => 'nullable|numeric|min:0|max:100',
            'odds_ratio' => 'nullable|integer|min:1',
            'minimum_participants' => 'nullable|integer|min:1',
            'winner_exclusion_months' => 'nullable|integer|min:0',
            'draw_start_day' => 'nullable|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'draw_start_time' => 'nullable|date_format:H:i',
            'draw_end_day' => 'nullable|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'draw_end_time' => 'nullable|date_format:H:i',
        ]);

        try {
            $settings = DrawAutomateSetting::firstOrCreate([]);

            $settings->update([
                'admin_fee_percentage' => $request->admin_fee_percentage ?? 7.50,
                'odds_ratio' => $request->odds_ratio ?? 400,
                'minimum_participants' => $request->minimum_participants ?? 100,
                'winner_exclusion_months' => $request->winner_exclusion_months ?? 6,
                'draw_start_day' => $request->draw_start_day ?? 'Monday',
                'draw_start_time' => $request->draw_start_time ?? '00:00:00',
                'draw_end_day' => $request->draw_end_day ?? 'Sunday',
                'draw_end_time' => $request->draw_end_time ?? '17:00:00',
            ]);

            return back()->with('t-success', 'Draw Automation settings updated successfully.');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}
