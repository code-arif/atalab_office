<?php

namespace App\Http\Controllers\Calendar;

use Exception;
use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\GoogleCalendarService;

class GetEventFromGoogleController extends Controller
{
    protected $googleCalendar;

    // serivce injection
    public function __construct(GoogleCalendarService $googleCalendar)
    {
        $this->googleCalendar = $googleCalendar;
    }

    // get events from google calendar
    public function getEvents(Request $request)
    {
        try {
            $start = $request->get('start');
            $end = $request->get('end');
            $teamId = $request->get('team_id');
            $status = $request->get('status');
            $categoryId = $request->get('category_id');

            $query = Work::with(['team', 'category']);

            // Date range filter - UPDATED for new schema
            if ($start && $end) {
                $query->whereBetween('start_datetime', [
                    Carbon::parse($start)->startOfDay(),
                    Carbon::parse($end)->endOfDay()
                ]);
            }

            // Team filter
            if ($teamId) {
                $query->where('team_id', $teamId);
            }

            // Status filter
            if ($status === 'completed') {
                $query->where('is_completed', true);
            } elseif ($status === 'pending') {
                $query->where('is_completed', false);
            } elseif ($status === 'rescheduled') {
                $query->where('is_rescheduled', true);
            }

            // Category filter
            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            $works = $query->get();

            $events = $works->map(function ($work) {
                try {
                    // Handle both all-day and timed events
                    if ($work->is_all_day) {
                        return [
                            'id' => $work->id,
                            'title' => $work->title,
                            'start' => Carbon::parse($work->start_datetime)->toDateString(),
                            'end' => Carbon::parse($work->end_datetime)->toDateString(),
                            'allDay' => true,
                            'description' => $work->description,
                            'location' => $work->location,
                            'backgroundColor' => $this->getEventColor($work),
                            'borderColor' => $this->getEventBorderColor($work),
                            'extendedProps' => [
                                'team' => $work->team ? $work->team->name : null,
                                'category' => $work->category ? $work->category->name : null,
                                'completed' => $work->is_completed,
                                'rescheduled' => $work->is_rescheduled,
                                'latitude' => $work->latitude,
                                'longitude' => $work->longitude,
                                'note' => $work->note,
                                'google_event_id' => $work->google_event_id,
                                'is_all_day' => true,
                            ],
                        ];
                    } else {
                        return [
                            'id' => $work->id,
                            'title' => $work->title,
                            'start' => Carbon::parse($work->start_datetime)->toIso8601String(),
                            'end' => Carbon::parse($work->end_datetime)->toIso8601String(),
                            'description' => $work->description,
                            'location' => $work->location,
                            'backgroundColor' => $this->getEventColor($work),
                            'borderColor' => $this->getEventBorderColor($work),
                            'extendedProps' => [
                                'team' => $work->team ? $work->team->name : null,
                                'category' => $work->category ? $work->category->name : null,
                                'completed' => $work->is_completed,
                                'rescheduled' => $work->is_rescheduled,
                                'latitude' => $work->latitude,
                                'longitude' => $work->longitude,
                                'note' => $work->note,
                                'google_event_id' => $work->google_event_id,
                                'is_all_day' => false,
                            ],
                        ];
                    }
                } catch (Exception $e) {
                    Log::error('Error processing work for calendar', [
                        'work_id' => $work->id,
                        'error' => $e->getMessage()
                    ]);
                    return null;
                }
            })->filter()->values();

            return response()->json($events);
        } catch (Exception $e) {
            Log::error('getEvents error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to load events',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    // event color based on status
    private function getEventColor($work)
    {
        if ($work->is_completed) {
            return '#10b981';
        } elseif ($work->is_rescheduled) {
            return '#f59e0b';
        }
        return '#3b82f6';
    }

    // event border color based on status
    private function getEventBorderColor($work)
    {
        if ($work->is_completed) {
            return '#059669';
        } elseif ($work->is_rescheduled) {
            return '#d97706';
        }
        return '#2563eb';
    }
}
