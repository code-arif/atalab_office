<?php

namespace App\Http\Controllers\Api\Chat;

use Exception;
use App\Models\Chat;
use App\Models\Room;
use App\Models\User;
use App\Models\GuestUser;
use App\Events\TypingEvent;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Events\MessageSendEvent;
use Illuminate\Http\JsonResponse;
use App\Events\MessageStatusEvent;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ChatApiController extends Controller
{
    /**
     * Guest Registration & Get Admin Info
     */
    public function guestRegister(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Check if guest already exists
        $guest = GuestUser::where('email', $request->email)->first();

        if (!$guest) {
            $guest = GuestUser::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'session_id' => Str::uuid(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_activity_at' => now()
            ]);
        } else {
            $guest->update(['last_activity_at' => now()]);
        }

        // Get admin user
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'guest' => [
                    'id' => $guest->id,
                    'name' => $guest->name,
                    'email' => $guest->email,
                    'phone' => $guest->phone,
                    'session_id' => $guest->session_id,
                ],
                'admin' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'avatar' => $admin->avatar ? url($admin->avatar) : asset('default/profile.jpg'),
                    'role' => $admin->role,
                ],
                'automated_message' => 'Thank you! We have received your message. Please wait 30 minutes, you will be contacted shortly.'
            ]
        ]);
    }

    /**
     * Guest Send Message to Admin
     */
    public function guestSendMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:guest_users,session_id',
            'text' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $guest = GuestUser::where('session_id', $request->session_id)->first();
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found'
            ], 404);
        }

        // Find or create room
        $room = Room::findOrCreateRoom(
            $guest->id,
            GuestUser::class,
            $admin->id,
            User::class
        );

        // Create message
        $chat = Chat::create([
            'sender_id' => $guest->id,
            'sender_type' => GuestUser::class,
            'receiver_id' => $admin->id,
            'receiver_type' => User::class,
            'text' => $request->text,
            'room_id' => $room->id,
            'status' => 'sent',
        ]);

        // Send email notification to admin (only first time)
        if (!$guest->admin_notified) {
            try {
                // Mail::to($admin->email)->send(new NewChatNotificationMail($guest, $chat));
                $guest->update([
                    'admin_notified' => true,
                    'first_message_at' => now()
                ]);
            } catch (Exception $e) {
                Log::error('Failed to send email: ' . $e->getMessage());
            }
        }

        $guest->updateActivity();

        // Load relationships
        $chat->load(['sender', 'receiver', 'room']);

        // Broadcast event
        broadcast(new MessageSendEvent($chat))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => ['chat' => $chat]
        ]);
    }

    /**
     * Guest Get Conversation with Admin
     */
    public function guestGetConversation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:guest_users,session_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $guest = GuestUser::where('session_id', $request->session_id)->first();
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found'
            ], 404);
        }

        // Get room
        $room = Room::where(function ($query) use ($guest, $admin) {
            $query->where('user_one_id', $guest->id)
                ->where('user_one_type', GuestUser::class)
                ->where('user_two_id', $admin->id)
                ->where('user_two_type', User::class);
        })->orWhere(function ($query) use ($guest, $admin) {
            $query->where('user_one_id', $admin->id)
                ->where('user_one_type', User::class)
                ->where('user_two_id', $guest->id)
                ->where('user_two_type', GuestUser::class);
        })->first();

        $messages = [];
        if ($room) {
            $messages = Chat::between(
                $guest->id,
                GuestUser::class,
                $admin->id,
                User::class
            )
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

            // Mark as read
            Chat::where('receiver_id', $guest->id)
                ->where('receiver_type', GuestUser::class)
                ->where('status', '!=', 'read')
                ->each(function ($chat) {
                    $chat->markAsRead();
                    broadcast(new MessageStatusEvent($chat))->toOthers();
                });
        }

        $guest->updateActivity();

        return response()->json([
            'success' => true,
            'message' => 'Conversation retrieved successfully',
            'data' => [
                'guest' => $guest,
                'admin' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'avatar' => $admin->avatar ? url($admin->avatar) : asset('default/profile.jpg'),
                ],
                'room' => $room,
                'messages' => $messages,
            ]
        ]);
    }

    /**
     * Guest Typing Indicator
     */
    public function guestTyping(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:guest_users,session_id',
            'room_id' => 'required|exists:rooms,id',
            'is_typing' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $guest = GuestUser::where('session_id', $request->session_id)->first();

        broadcast(new TypingEvent(
            $request->room_id,
            $guest->id,
            $request->is_typing,
            'guest'
        ))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Typing status updated'
        ]);
    }
}
