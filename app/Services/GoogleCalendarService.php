<?php

namespace App\Services;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    protected $client;
    protected $service;
    protected $calendar;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setApplicationName(config('app.name'));
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect_uri'));
        $this->client->addScope(Calendar::CALENDAR);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    // exchange auth code for access token
    public function authenticate($code)
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new Exception('Error fetching access token: ' . $token['error']);
        }

        return $token;
    }

    // set access token and refresh if needed
    public function setAccessToken($token)
    {
        try {
            // Validate token structure
            if (!is_array($token)) {
                Log::error('Invalid token format - not an array', [
                    'token_type' => gettype($token),
                    'token_value' => is_string($token) ? substr($token, 0, 50) . '...' : $token
                ]);
                throw new Exception('Invalid token format: Expected array, got ' . gettype($token));
            }

            if (!isset($token['access_token'])) {
                Log::error('Token missing access_token', [
                    'token_keys' => array_keys($token)
                ]);
                throw new Exception('Token missing access_token');
            }

            Log::info('Setting access token', [
                'has_access_token' => isset($token['access_token']),
                'has_refresh_token' => isset($token['refresh_token']),
                'expires_in' => $token['expires_in'] ?? 'N/A',
                'token_type' => $token['token_type'] ?? 'N/A'
            ]);

            $this->client->setAccessToken($token);

            // Check if token is expired and refresh if needed
            if ($this->client->isAccessTokenExpired()) {
                Log::info('Token is expired, attempting refresh');

                $refreshToken = $this->client->getRefreshToken() ?? $token['refresh_token'] ?? null;

                if (!$refreshToken) {
                    Log::error('No refresh token available', [
                        'client_refresh_token' => $this->client->getRefreshToken(),
                        'token_has_refresh' => isset($token['refresh_token'])
                    ]);
                    throw new Exception('Token expired and no refresh token available. Please reconnect Google Calendar.');
                }

                Log::info('Refreshing token with refresh_token');

                $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);

                if (isset($newToken['error'])) {
                    Log::error('Token refresh failed', [
                        'error' => $newToken['error'],
                        'error_description' => $newToken['error_description'] ?? 'N/A'
                    ]);
                    throw new Exception('Error refreshing token: ' . $newToken['error']);
                }

                // Preserve refresh token if not in new response
                if (!isset($newToken['refresh_token']) && $refreshToken) {
                    $newToken['refresh_token'] = $refreshToken;
                }

                Log::info('Token refreshed successfully', [
                    'new_expires_in' => $newToken['expires_in'] ?? 'N/A'
                ]);

                // Initialize calendar service after refresh
                $this->service = new \Google_Service_Calendar($this->client);

                return $newToken;
            }

            // Initialize calendar service for non-expired token
            $this->service = new \Google_Service_Calendar($this->client);

            Log::info('Calendar service initialized successfully');

            return null;
        } catch (Exception $e) {
            Log::error('Google Set Token Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    // create event in google calendar from this internal project
    public function createEvent($work, $calendarId = 'primary')
    {
        try {
            if (!$this->service) {
                throw new Exception('Calendar service not initialized. Call setAccessToken first.');
            }

            if ($work->is_all_day) {
                // All day event
                $event = new Event([
                    'summary' => $work->title,
                    'description' => $work->description,
                    'location' => $work->location,
                    'start' => [
                        'date' => Carbon::parse($work->start_datetime)->toDateString(),
                        'timeZone' => config('app.timezone', 'Asia/Dhaka'),
                    ],
                    'end' => [
                        'date' => Carbon::parse($work->end_datetime)->toDateString(),
                        'timeZone' => config('app.timezone', 'Asia/Dhaka'),
                    ],
                ]);
            } else {
                // Specific time event
                $event = new Event([
                    'summary' => $work->title,
                    'description' => $work->description,
                    'location' => $work->location,
                    'start' => [
                        'dateTime' => Carbon::parse($work->start_datetime)->toRfc3339String(),
                        'timeZone' => config('app.timezone', 'Asia/Dhaka'),
                    ],
                    'end' => [
                        'dateTime' => Carbon::parse($work->end_datetime)->toRfc3339String(),
                        'timeZone' => config('app.timezone', 'Asia/Dhaka'),
                    ],
                ]);
            }

            $createdEvent = $this->service->events->insert($calendarId, $event);

            Log::info('Google Calendar event created', [
                'event_id' => $createdEvent->getId(),
                'work_id' => $work->id
            ]);

            return $createdEvent->getId();
        } catch (Exception $e) {
            Log::error('Google Calendar Create Error', [
                'error' => $e->getMessage(),
                'work_id' => $work->id
            ]);
            throw $e;
        }
    }


    // update existing google calendar event from this internal project
    public function updateEvent($work, $calendarId = 'primary')
    {
        try {
            if (!$this->service) {
                throw new Exception('Calendar service not initialized. Call setAccessToken first.');
            }

            if (!$work->google_event_id) {
                throw new Exception('No Google event ID found for this work');
            }

            // Get existing event
            $event = $this->service->events->get($calendarId, $work->google_event_id);

            // Update event properties
            $event->setSummary($work->title);
            $event->setDescription($work->description);
            $event->setLocation($work->location);

            if ($work->is_all_day) {
                // All day event
                $event->setStart(new EventDateTime([
                    'date' => Carbon::parse($work->start_datetime)->toDateString(),
                    'timeZone' => config('app.timezone', 'Asia/Dhaka'),
                ]));

                $event->setEnd(new EventDateTime([
                    'date' => Carbon::parse($work->end_datetime)->toDateString(),
                    'timeZone' => config('app.timezone', 'Asia/Dhaka'),
                ]));
            } else {
                // Specific time event
                $event->setStart(new EventDateTime([
                    'dateTime' => Carbon::parse($work->start_datetime)->toRfc3339String(),
                    'timeZone' => config('app.timezone', 'Asia/Dhaka'),
                ]));

                $event->setEnd(new EventDateTime([
                    'dateTime' => Carbon::parse($work->end_datetime)->toRfc3339String(),
                    'timeZone' => config('app.timezone', 'Asia/Dhaka'),
                ]));
            }

            $updatedEvent = $this->service->events->update($calendarId, $work->google_event_id, $event);

            Log::info('Google Calendar event updated', [
                'event_id' => $updatedEvent->getId(),
                'work_id' => $work->id
            ]);

            return $updatedEvent->getId();
        } catch (Exception $e) {
            Log::error('Google Calendar Update Error', [
                'error' => $e->getMessage(),
                'work_id' => $work->id,
                'google_event_id' => $work->google_event_id
            ]);
            throw $e;
        }
    }


    // Fixed delete event method
    public function deleteEvent($eventId, $calendarId = 'primary')
    {
        try {
            if (!$this->service) {
                throw new Exception('Calendar service not initialized. Call setAccessToken first.');
            }

            if (empty($eventId)) {
                throw new Exception('Event ID is required');
            }

            $this->service->events->delete($calendarId, $eventId);

            Log::info('Google Calendar event deleted', [
                'event_id' => $eventId,
                'calendar_id' => $calendarId
            ]);

            return true;
        } catch (\Google_Service_Exception $e) {
            // Handle Google API specific errors
            $errors = json_decode($e->getMessage(), true);
            Log::error('Google Calendar Delete Error (API)', [
                'error' => $e->getMessage(),
                'event_id' => $eventId,
                'status_code' => $e->getCode(),
                'errors' => $errors
            ]);

            // If event doesn't exist (404), consider it successful
            if ($e->getCode() === 404 || $e->getCode() === 410) {
                Log::info('Google Calendar event already deleted or not found', [
                    'event_id' => $eventId
                ]);
                return true;
            }

            throw new Exception('Google Calendar API Error: ' . $e->getMessage());
        } catch (Exception $e) {
            Log::error('Google Calendar Delete Error', [
                'error' => $e->getMessage(),
                'event_id' => $eventId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    // list events from google calendar
    public function listEvents($startDate = null, $endDate = null, $retries = 3)
    {
        if (!$this->service) {
            throw new Exception('Calendar service not initialized');
        }

        $attempt = 0;
        $lastError = null;

        while ($attempt < $retries) {
            try {
                $optParams = [
                    'maxResults' => 2500,
                    'orderBy' => 'startTime',
                    'singleEvents' => true,
                    'timeMin' => $startDate
                        ? Carbon::parse($startDate)->toRfc3339String()
                        : Carbon::now()->subMonths(3)->startOfMonth()->toRfc3339String(), // 3 months ago
                    'timeMax' => $endDate
                        ? Carbon::parse($endDate)->toRfc3339String()
                        : Carbon::now()->addMonths(3)->endOfMonth()->toRfc3339String(), // 3 months ahead
                ];

                $calendarId = config('services.google.calendar_id', 'primary');

                Log::info('Fetching Google Calendar events', [
                    'attempt' => $attempt + 1,
                    'timeMin' => $optParams['timeMin'],
                    'timeMax' => $optParams['timeMax']
                ]);

                $results = $this->service->events->listEvents($calendarId, $optParams);

                Log::info('Google Calendar events fetched successfully', [
                    'count' => count($results->getItems()),
                    'attempt' => $attempt + 1
                ]);

                return $results->getItems();
            } catch (Exception $e) {
                $lastError = $e;
                $attempt++;

                Log::warning('Google Calendar fetch attempt failed', [
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                    'retrying' => $attempt < $retries
                ]);

                if ($attempt < $retries) {
                    // Wait before retry: 1s, 2s, 3s
                    sleep($attempt);
                }
            }
        }

        // All attempts failed
        Log::error('Google Calendar List Error - All retries exhausted', [
            'error' => $lastError->getMessage(),
            'attempts' => $retries
        ]);

        throw $lastError;
    }
}
