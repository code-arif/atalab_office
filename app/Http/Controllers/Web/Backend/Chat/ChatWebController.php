<?php

namespace App\Http\Controllers\Web\Backend\Chat;

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
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatWebController extends Controller
{
    /**
     * Display the chat view
     */
    public function index()
    {
        return view('backend.layouts.chatting.index');
    }

    /**
     * Get user list with unread counts (Admin sees guests + users)
     */
    public function list()
    {
        $authUser = Auth::user();

        if (!$authUser) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Get registered users
        $registeredUsers = User::select('id', 'name', 'email', 'avatar', 'role')
            ->where('id', '!=', $authUser->id)
            ->where(function ($q) use ($authUser) {
                $q->whereHas('sentMessages', fn($sq) => $sq
                    ->where('receiver_id', $authUser->id)
                    ->where('receiver_type', User::class))
                    ->orWhereHas('receivedMessages', fn($sq) => $sq
                        ->where('sender_id', $authUser->id)
                        ->where('sender_type', User::class));
            })
            ->get()
            ->map(function ($user) {
                $user->is_guest = false;
                $user->display_name = $user->name;
                $user->display_avatar = $user->avatar ? url($user->avatar) : asset('default/profile.jpg');
                $user->user_type = User::class;
                return $user;
            });

        // Get guest users
        $guestUsers = GuestUser::where(function ($q) use ($authUser) {
            $q->whereHas('sentMessages', fn($sq) => $sq
                ->where('receiver_id', $authUser->id)
                ->where('receiver_type', User::class))
                ->orWhereHas('receivedMessages', fn($sq) => $sq
                    ->where('sender_id', $authUser->id)
                    ->where('sender_type', GuestUser::class));
        })
            ->get()
            ->map(function ($guest) {
                $guest->is_guest = true;
                $guest->display_name = $guest->name ?? 'Guest User';
                $guest->display_avatar = asset('default/profile.jpg');
                $guest->role = 'Guest';
                $guest->user_type = GuestUser::class;
                return $guest;
            });

        // Combine both
        $allConversations = $registeredUsers->merge($guestUsers);

        // Attach last message + unread count
        $allConversations = $allConversations->map(function ($user) use ($authUser) {
            $userType = $user->user_type;

            // Last message
            $lastChat = Chat::between(
                $authUser->id,
                User::class,
                $user->id,
                $userType
            )
                ->latest('created_at')
                ->first();

            // Unread count (incoming messages from this user)
            $unreadCount = Chat::where('sender_id', $user->id)
                ->where('sender_type', $userType)
                ->where('receiver_id', $authUser->id)
                ->where('receiver_type', User::class)
                ->where('status', '!=', 'read')
                ->count();

            $user->last_chat = $lastChat;
            $user->unread_count = $unreadCount;

            if ($lastChat) {
                $lastChat->humanize_date = \Carbon\Carbon::parse($lastChat->created_at)->diffForHumans();
                $lastChat->short_text = Str::limit($lastChat->text, 30);
            }

            return $user;
        });

        // Sort by last message time
        $sorted = $allConversations->sortByDesc(fn($u) => $u->last_chat?->created_at ?? now()->subYears(10))->values();

        return response()->json([
            'success' => true,
            'message' => 'Chat list retrieved successfully',
            'data' => ['users' => $sorted]
        ]);
    }

    /**
     * Search users
     */
    public function search(Request $request): JsonResponse
    {
        $user_id = Auth::id();
        $keyword = $request->get('keyword');

        $users = User::select('id', 'name', 'email', 'avatar', 'last_activity_at')
            ->where('id', '!=', $user_id)
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('email', 'LIKE', "%{$keyword}%");
            })
            ->get();

        // Search guest users
        $guestUsers = GuestUser::where(function ($query) use ($keyword) {
            $query->where('name', 'LIKE', "%{$keyword}%")
                ->orWhere('email', 'LIKE', "%{$keyword}%")
                ->orWhere('phone', 'LIKE', "%{$keyword}%");
        })->get()->map(function ($guest) {
            $guest->is_guest = true;
            return $guest;
        });

        $allUsers = $users->concat($guestUsers);

        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully',
            'data' => ['users' => $allUsers],
        ]);
    }

    /**
     * Get conversation (with guest or user)
     */
    public function conversation(Request $request, $receiver_id): JsonResponse
    {
        $authUser = Auth::user();
        $receiver_type = $request->input('receiver_type', User::class);

        // Find receiver
        $receiver = $receiver_type === GuestUser::class
            ? GuestUser::find($receiver_id)
            : User::find($receiver_id);

        if (!$receiver) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Mark as read
        Chat::where('receiver_id', $authUser->id)
            ->where('receiver_type', User::class)
            ->where('sender_id', $receiver_id)
            ->where('sender_type', $receiver_type)
            ->where('status', '!=', 'read')
            ->each(function ($chat) {
                $chat->markAsRead();
                broadcast(new MessageStatusEvent($chat))->toOthers();
            });

        // Get messages
        $chat = Chat::between(
            $authUser->id,
            User::class,
            $receiver_id,
            $receiver_type
        )
            ->with(['sender', 'receiver', 'room'])
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get();

        // Get or create room
        $room = Room::findOrCreateRoom(
            $authUser->id,
            User::class,
            $receiver_id,
            $receiver_type
        );

        $data = [
            'receiver' => [
                'id' => $receiver->id,
                'name' => $receiver->name ?? 'Guest User',
                'email' => $receiver->email,
                'avatar' => $receiver->avatar ?? asset('default/profile.jpg'),
                'role' => $receiver->role ?? 'Guest',
                'is_guest' => $receiver_type === GuestUser::class,
                'user_type' => $receiver_type,
            ],
            'sender' => [
                'id' => $authUser->id,
                'name' => $authUser->name,
                'email' => $authUser->email,
                'avatar' => $authUser->avatar ? url($authUser->avatar) : asset('default/profile.jpg'),
            ],
            'room' => $room,
            'chat' => $chat
        ];

        return response()->json([
            'success' => true,
            'message' => 'Messages retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Send message (to guest or user)
     */
    public function send(Request $request, $receiver_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:1000',
            'receiver_type' => 'required|in:' . User::class . ',' . GuestUser::class,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $authUser = Auth::user();
        $receiver_type = $request->receiver_type;

        // Find receiver
        $receiver = $receiver_type === GuestUser::class
            ? GuestUser::find($receiver_id)
            : User::find($receiver_id);

        if (!$receiver) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Get or create room
        $room = Room::findOrCreateRoom(
            $authUser->id,
            User::class,
            $receiver_id,
            $receiver_type
        );

        // Create message
        $chat = Chat::create([
            'sender_id' => $authUser->id,
            'sender_type' => User::class,
            'receiver_id' => $receiver_id,
            'receiver_type' => $receiver_type,
            'text' => $request->text,
            'room_id' => $room->id,
            'status' => 'sent',
        ]);

        $chat->load(['sender', 'receiver', 'room']);

        broadcast(new MessageSendEvent($chat))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => ['chat' => $chat]
        ]);
    }


    /**
     * Edit message (Admin only)
     */
    public function editMessage($message_id, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $authUser = Auth::user();

        $chat = Chat::where('id', $message_id)
            ->where('sender_id', $authUser->id)
            ->first();

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found or unauthorized'
            ], 404);
        }

        $chat->update([
            'text' => $request->text,
            'is_edited' => true,
            'edited_at' => now()
        ]);

        $chat->load(['sender', 'receiver', 'room']);

        broadcast(new MessageSendEvent($chat))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Message updated successfully',
            'data' => ['chat' => $chat]
        ]);
    }

    /**
     * Delete single message (Admin only)
     */
    public function deleteMessage($message_id): JsonResponse
    {
        $authUser = Auth::user();

        $chat = Chat::where('id', $message_id)
            ->where('sender_id', $authUser->id)
            ->first();

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found or unauthorized'
            ], 404);
        }

        $chat->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully'
        ]);
    }

    /**
     * Delete conversation
     */
    public function deleteChat($receiver_id): JsonResponse
    {
        $sender_id = Auth::id();

        $room = Room::where(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $sender_id)->where('user_two_id', $receiver_id);
        })->orWhere(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $receiver_id)->where('user_two_id', $sender_id);
        })->first();

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found'
            ], 404);
        }

        Chat::where('room_id', $room->id)->delete();
        $room->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conversation deleted successfully'
        ]);
    }

    /**
     * Typing indicator
     */
    public function typing(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'is_typing' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user_id = Auth::id();

        broadcast(new TypingEvent(
            $request->room_id,
            $user_id,
            $request->is_typing,
            'admin'
        ))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Typing status updated'
        ]);
    }

    /**
     * Mark all as read
     */
    public function seenAll($receiver_id): JsonResponse
    {
        $sender_id = Auth::id();

        Chat::where('receiver_id', $sender_id)
            ->where('sender_id', $receiver_id)
            ->where('status', '!=', 'read')
            ->each(function ($chat) {
                $chat->markAsRead();
                broadcast(new MessageStatusEvent($chat))->toOthers();
            });

        return response()->json([
            'success' => true,
            'message' => 'All messages marked as read'
        ]);
    }

    /**
     * Mark single as read
     */
    public function seenSingle($chat_id): JsonResponse
    {
        $sender_id = Auth::id();

        $chat = Chat::where('id', $chat_id)
            ->where('receiver_id', $sender_id)
            ->first();

        if ($chat && $chat->status !== 'read') {
            $chat->markAsRead();
            broadcast(new MessageStatusEvent($chat))->toOthers();
        }

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read'
        ]);
    }

    /**
     * Get room
     */
    public function getRoom($receiver_id): JsonResponse
    {
        $sender_id = Auth::id();

        $room = Room::with([
            'userOne:id,name,email,avatar,last_activity_at',
            'userTwo:id,name,email,avatar,last_activity_at'
        ])
            ->where(function ($query) use ($receiver_id, $sender_id) {
                $query->where('user_one_id', $receiver_id)->where('user_two_id', $sender_id);
            })->orWhere(function ($query) use ($receiver_id, $sender_id) {
                $query->where('user_one_id', $sender_id)->where('user_two_id', $receiver_id);
            })->first();

        if (!$room) {
            $room = Room::create([
                'user_one_id' => $sender_id,
                'user_two_id' => $receiver_id,
            ]);
            $room->load(['userOne', 'userTwo']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Room retrieved successfully',
            'data' => ['room' => $room]
        ]);
    }
}
