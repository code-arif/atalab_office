<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'user_one_id',
        'user_one_type',
        'user_two_id',
        'user_two_type'
    ];

    // Polymorphic Relationships
    public function userOne()
    {
        return $this->morphTo('userOne', 'user_one_type', 'user_one_id');
    }

    public function userTwo()
    {
        return $this->morphTo('userTwo', 'user_two_type', 'user_two_id');
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    // Helper: Get the other participant
    public function getOtherUser($currentUserId, $currentUserType)
    {
        if ($this->user_one_id == $currentUserId && $this->user_one_type == $currentUserType) {
            return $this->userTwo;
        }
        return $this->userOne;
    }

    // Static: Find or create room between two users
    public static function findOrCreateRoom($user1Id, $user1Type, $user2Id, $user2Type)
    {
        $room = self::where(function ($query) use ($user1Id, $user1Type, $user2Id, $user2Type) {
            $query->where('user_one_id', $user1Id)
                ->where('user_one_type', $user1Type)
                ->where('user_two_id', $user2Id)
                ->where('user_two_type', $user2Type);
        })->orWhere(function ($query) use ($user1Id, $user1Type, $user2Id, $user2Type) {
            $query->where('user_one_id', $user2Id)
                ->where('user_one_type', $user2Type)
                ->where('user_two_id', $user1Id)
                ->where('user_two_type', $user1Type);
        })->first();

        if (!$room) {
            $room = self::create([
                'user_one_id' => $user1Id,
                'user_one_type' => $user1Type,
                'user_two_id' => $user2Id,
                'user_two_type' => $user2Type,
            ]);
        }

        return $room;
    }
}
