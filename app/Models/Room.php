<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_one_id',
        'user_one_type',
        'user_two_id',
        'user_two_type',
    ];

    /**
     * Get user one (polymorphic)
     */
    public function userOne()
    {
        return $this->morphTo('userOne', 'user_one_type', 'user_one_id');
    }

    /**
     * Get user two (polymorphic)
     */
    public function userTwo()
    {
        return $this->morphTo('userTwo', 'user_two_type', 'user_two_id');
    }

    /**
     * Get all chats in this room
     */
    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    /**
     * Find or create room between two users
     */
    public static function findOrCreateRoom($userOneId, $userOneType, $userTwoId, $userTwoType)
    {
        // Try to find existing room (check both directions)
        $room = self::where(function ($query) use ($userOneId, $userOneType, $userTwoId, $userTwoType) {
            $query->where('user_one_id', $userOneId)
                ->where('user_one_type', $userOneType)
                ->where('user_two_id', $userTwoId)
                ->where('user_two_type', $userTwoType);
        })->orWhere(function ($query) use ($userOneId, $userOneType, $userTwoId, $userTwoType) {
            $query->where('user_one_id', $userTwoId)
                ->where('user_one_type', $userTwoType)
                ->where('user_two_id', $userOneId)
                ->where('user_two_type', $userOneType);
        })->first();

        // If room doesn't exist, create it
        if (!$room) {
            $room = self::create([
                'user_one_id' => $userOneId,
                'user_one_type' => $userOneType,
                'user_two_id' => $userTwoId,
                'user_two_type' => $userTwoType,
            ]);
        }

        return $room;
    }

    /**
     * Check if user is participant in this room
     */
    public function hasParticipant($userId, $userType)
    {
        return ($this->user_one_id == $userId && $this->user_one_type == $userType) ||
            ($this->user_two_id == $userId && $this->user_two_type == $userType);
    }

    /**
     * Get the other participant in the room
     */
    public function getOtherParticipant($userId, $userType)
    {
        if ($this->user_one_id == $userId && $this->user_one_type == $userType) {
            return $this->userTwo;
        }
        return $this->userOne;
    }
}
