<?php

use App\Models\Room;
use App\Models\User;
use App\Models\GuestUser;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

/**
 * Chat Room Channel
 * Anyone in the room can listen (guest or authenticated user)
 */
Broadcast::channel('chat-room.{room_id}', function ($user, $room_id) {
    $room = Room::find($room_id);

    if (!$room) return false;

    // Check if user is a participant (could be User or GuestUser)
    if ($user instanceof User) {
        return ($user->id == $room->user_one_id && $room->user_one_type == User::class) ||
            ($user->id == $room->user_two_id && $room->user_two_type == User::class);
    }

    if ($user instanceof GuestUser) {
        return ($user->id == $room->user_one_id && $room->user_one_type == GuestUser::class) ||
            ($user->id == $room->user_two_id && $room->user_two_type == GuestUser::class);
    }

    return false;
});

/**
 * Receiver Channel
 * For authenticated users receiving messages
 */
Broadcast::channel('chat-receiver.{receiver_id}', function ($user, $receiver_id) {
    if ($user instanceof User) {
        return (int) $user->id === (int) $receiver_id;
    }
    return false;
});

/**
 * Sender Channel
 * For message status updates
 */
Broadcast::channel('chat-sender.{sender_id}', function ($user, $sender_id) {
    if ($user instanceof User) {
        return (int) $user->id === (int) $sender_id;
    }
    return false;
});

/**
 * Guest Channel (Public for unauthenticated)
 * For guest users to receive messages
 * NOTE: This is a public channel since guests aren't authenticated
 */
Broadcast::channel('guest-chat.{session_id}', function ($user, $session_id) {
    // For public access, always return true
    // Security: validate session_id on backend before broadcasting
    return true;
});
