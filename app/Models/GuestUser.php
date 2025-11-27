<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class GuestUser extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'session_id',
        'admin_notified',
        'first_message_at',
        'last_activity_at',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'admin_notified' => 'boolean',
        'first_message_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->session_id) {
                $model->session_id = Str::uuid();
            }
        });
    }

    public function chats()
    {
        return $this->morphMany(Chat::class, 'sender');
    }

    public function updateActivity()
    {
        $this->update(['last_activity_at' => now()]);
    }


    public function sentMessages()
    {
        return $this->morphMany(Chat::class, 'sender', 'sender_type', 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->morphMany(Chat::class, 'receiver', 'receiver_type', 'receiver_id');
    }
}
