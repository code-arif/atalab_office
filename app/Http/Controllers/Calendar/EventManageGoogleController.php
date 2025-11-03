<?php

namespace App\Http\Controllers\Calendar;

use Exception;
use App\Models\Work;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\Validator;

class EventManageGoogleController extends Controller
{
    protected $googleCalendar;

    // serivce injection
    public function __construct(GoogleCalendarService $googleCalendar)
    {
        $this->googleCalendar = $googleCalendar;
    }

    //work store and sync with google calendar
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'title'         => 'required|string|max:255',
                'description'   => 'nullable|string',
                'location'      => 'nullable|string',
                'latitude'      => 'nullable|numeric|between:-90,90',
                'longitude'     => 'nullable|numeric|between:-180,180',
                'start_time'    => 'nullable|date_format:h:i A',
                'end_time'      => 'nullable|date_format:h:i A',
                'work_date'     => 'required|date',
                'is_all_day'    => 'nullable|boolean',
                'team_id'       => 'nullable|exists:teams,id',
                'category_id'   => 'nullable|exists:categories,id',
                'category_name' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            // Handle Category
            $categoryId = $request->category_id;
            if (!$categoryId && $request->category_name) {
                $category = Category::create([
                    'name' => $request->category_name,
                ]);
                $categoryId = $category->id;
            }

            // Prepare DateTime fields
            $isAllDay = $request->is_all_day ?? false;

            if ($isAllDay) {
                // All day event
                $startDatetime = Carbon::parse($request->work_date)->startOfDay();
                $endDatetime = Carbon::parse($request->work_date)->endOfDay();
            } else {
                // Specific time event - Convert 12-hour to datetime
                $startDatetime = Carbon::parse($request->work_date . ' ' . $request->start_time);
                $endDatetime = Carbon::parse($request->work_date . ' ' . $request->end_time);
            }

            // Save Work
            $work = Work::create([
                'user_id'         => auth()->id(), // Add user_id
                'title'           => $request->title,
                'description'     => $request->description,
                'location'        => $request->location,
                'latitude'        => $request->latitude,
                'longitude'       => $request->longitude,
                'start_datetime'  => $startDatetime,
                'end_datetime'    => $endDatetime,
                'is_all_day'      => $isAllDay,
                'team_id'         => $request->team_id,
                'category_id'     => $categoryId,
            ]);

            // Google Calendar Sync
            $user = auth()->user();
            if ($user && $user->google_access_token) {
                try {
                    Log::info('Attempting Google Calendar sync for new work', [
                        'work_id' => $work->id,
                        'user_id' => $user->id
                    ]);

                    // Decode token properly
                    $token = json_decode($user->google_access_token, true);

                    // If it's not an array, means it's corrupted or just string
                    if (!is_array($token)) {
                        Log::warning('Token is not in proper format, skipping Google sync', [
                            'token_preview' => substr($user->google_access_token, 0, 50)
                        ]);
                        goto skip_google_sync;
                    }

                    // Add refresh token from database if missing
                    if (!isset($token['refresh_token']) && $user->google_refresh_token) {
                        $token['refresh_token'] = $user->google_refresh_token;
                        Log::info('Added refresh token from database');
                    }

                    // Set access token
                    $newToken = $this->googleCalendar->setAccessToken($token);

                    // Update token if refreshed
                    if ($newToken) {
                        Log::info('Token was refreshed during work creation');

                        $user->google_access_token = json_encode($newToken);

                        if (isset($newToken['refresh_token'])) {
                            $user->google_refresh_token = $newToken['refresh_token'];
                        }

                        if (isset($newToken['expires_in'])) {
                            $user->google_token_expires_at = Carbon::now()->addSeconds($newToken['expires_in']);
                        }

                        $user->save();
                    }

                    // CREATE EVENT IN GOOGLE CALENDAR
                    $googleEventId = $this->googleCalendar->createEvent($work);

                    // Update work with Google event ID
                    $work->update([
                        'google_event_id' => $googleEventId,
                        'google_synced_at' => Carbon::now(),
                    ]);

                    Log::info('Work synced to Google Calendar successfully', [
                        'work_id' => $work->id,
                        'google_event_id' => $googleEventId
                    ]);
                } catch (Exception $e) {
                    Log::error('Google Calendar sync failed during work creation', [
                        'work_id' => $work->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Don't fail the whole operation
                }
            }

            skip_google_sync:

            DB::commit();

            // Reload work with relationships
            $work->load(['team', 'category']);

            return response()->json([
                'status'  => true,
                'message' => 'Work created successfully!',
                'data'    => $work,
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error creating work', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create work: ' . $e->getMessage()
            ], 500);
        }
    }


    // show work details
    public function show(Work $work)
    {
        $work->load(['team', 'category']);
        return response()->json($work);
    }

    // work update and sync with google calendar
    public function update(Request $request, Work $work)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'title'         => 'required|string|max:255',
                'description'   => 'nullable|string',
                'location'      => 'nullable|string',
                'latitude'      => 'nullable|numeric|between:-90,90',
                'longitude'     => 'nullable|numeric|between:-180,180',
                'start_time'    => 'nullable|date_format:h:i A',
                'end_time'      => 'nullable|date_format:h:i A',
                'work_date'     => 'required|date',
                'is_all_day'    => 'nullable|boolean',
                'team_id'       => 'nullable|exists:teams,id',
                'category_id'   => 'nullable|exists:categories,id',
                'category_name' => 'nullable|string|max:255',
                'note'          => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            // Handle Category
            $categoryId = $request->category_id;
            if (!$categoryId && $request->category_name) {
                $category = Category::firstOrCreate(
                    ['name' => $request->category_name],
                    ['name' => $request->category_name]
                );
                $categoryId = $category->id;
            }

            // Prepare DateTime fields
            $isAllDay = $request->is_all_day ?? false;

            if ($isAllDay) {
                // All day event
                $startDatetime = Carbon::parse($request->work_date)->startOfDay();
                $endDatetime = Carbon::parse($request->work_date)->endOfDay();
            } else {
                // Specific time event - Convert 12-hour to datetime
                $startDatetime = Carbon::parse($request->work_date . ' ' . $request->start_time);
                $endDatetime = Carbon::parse($request->work_date . ' ' . $request->end_time);
            }


            // Update Work
            $work->update([
                'title'           => $request->title,
                'description'     => $request->description,
                'location'        => $request->location,
                'latitude'        => $request->latitude,
                'longitude'       => $request->longitude,
                'start_datetime'  => $startDatetime,
                'end_datetime'    => $endDatetime,
                'is_all_day'      => $isAllDay,
                'team_id'         => $request->team_id,
                'category_id'     => $categoryId,
                'note'            => $request->note,
            ]);

            // Google Calendar Sync
            $user = auth()->user();
            if ($user && $user->google_access_token && $work->google_event_id) {
                try {
                    Log::info('Attempting Google Calendar update', [
                        'work_id' => $work->id,
                        'google_event_id' => $work->google_event_id
                    ]);

                    $token = json_decode($user->google_access_token, true);

                    if (!is_array($token)) {
                        Log::warning('Token format invalid for update');
                        goto skip_google_update;
                    }

                    if (!isset($token['refresh_token']) && $user->google_refresh_token) {
                        $token['refresh_token'] = $user->google_refresh_token;
                    }

                    $newToken = $this->googleCalendar->setAccessToken($token);

                    if ($newToken) {
                        $user->google_access_token = json_encode($newToken);

                        if (isset($newToken['refresh_token'])) {
                            $user->google_refresh_token = $newToken['refresh_token'];
                        }

                        if (isset($newToken['expires_in'])) {
                            $user->google_token_expires_at = Carbon::now()->addSeconds($newToken['expires_in']);
                        }

                        $user->save();
                    }

                    // UPDATE EVENT IN GOOGLE CALENDAR
                    $this->googleCalendar->updateEvent($work);

                    $work->update([
                        'google_synced_at' => Carbon::now(),
                    ]);

                    Log::info('Work updated in Google Calendar successfully', [
                        'work_id' => $work->id,
                        'google_event_id' => $work->google_event_id
                    ]);
                } catch (Exception $e) {
                    Log::error('Google Calendar update failed', [
                        'work_id' => $work->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            skip_google_update:

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Work updated successfully!',
                'data'    => $work->load(['team', 'category']),
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error updating work', [
                'work_id' => $work->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to update work: ' . $e->getMessage()
            ], 500);
        }
    }


    public function destroy(Work $work)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            // Delete from Google Calendar first (if connected)
            if ($user->google_access_token && $work->google_event_id) {
                try {
                    Log::info('Attempting to delete Google Calendar event', [
                        'work_id' => $work->id,
                        'google_event_id' => $work->google_event_id
                    ]);

                    // Decode token properly
                    $token = json_decode($user->google_access_token, true);

                    if (!is_array($token)) {
                        Log::warning('Token format invalid, attempting string format', [
                            'token_preview' => substr($user->google_access_token, 0, 50)
                        ]);

                        // If token is just a string (corrupted), try to reconstruct
                        $token = [
                            'access_token' => $user->google_access_token,
                            'refresh_token' => $user->google_refresh_token,
                        ];
                    }

                    // Add refresh token from database if missing
                    if (!isset($token['refresh_token']) && $user->google_refresh_token) {
                        $token['refresh_token'] = $user->google_refresh_token;
                    }

                    // Set token and handle refresh
                    $newToken = $this->googleCalendar->setAccessToken($token);

                    // Update token if refreshed
                    if ($newToken) {
                        $user->google_access_token = json_encode($newToken);

                        if (isset($newToken['refresh_token'])) {
                            $user->google_refresh_token = $newToken['refresh_token'];
                        }

                        if (isset($newToken['expires_in'])) {
                            $user->google_token_expires_at = Carbon::now()->addSeconds($newToken['expires_in']);
                        }

                        $user->save();
                    }

                    // Delete the event from Google Calendar
                    $this->googleCalendar->deleteEvent($work->google_event_id);

                    Log::info('Google Calendar event deleted successfully', [
                        'work_id' => $work->id,
                        'google_event_id' => $work->google_event_id
                    ]);
                } catch (Exception $e) {
                    Log::error('Failed to delete from Google Calendar', [
                        'work_id' => $work->id,
                        'google_event_id' => $work->google_event_id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    // Don't fail the whole operation
                    // Continue to delete from local database
                }
            }

            // Delete from local database
            $work->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Work schedule deleted successfully!'
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error deleting work', [
                'work_id' => $work->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete work: ' . $e->getMessage()
            ], 500);
        }
    }
}
