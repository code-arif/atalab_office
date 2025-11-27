<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sender_id',
        'sender_type',
        'receiver_id',
        'receiver_type',
        'text',
        'file',
        'room_id',
        'status',
        'is_edited',
        'edited_at',
        'read_at',
        'delivered_at'
    ];

    protected $casts = [
        'sender_id' => 'integer',
        'receiver_id' => 'integer',
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
        'read_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected $appends = [
        'humanize_date',
        'short_text',
        'type',
        'status_info'
    ];

    // Polymorphic Relationships
    public function sender()
    {
        return $this->morphTo('sender', 'sender_type', 'sender_id');
    }

    public function receiver()
    {
        return $this->morphTo('receiver', 'receiver_type', 'receiver_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // Accessors
    public function getFileAttribute($value): ?string
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        return $value ? url($value) : null;
    }

    public function getShortTextAttribute(): ?string
    {
        if (!$this->text) return $this->file ? '📎 File' : null;
        return strlen($this->text) > 30 ? substr($this->text, 0, 30) . '...' : $this->text;
    }

    public function getHumanizeDateAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getTypeAttribute(): string
    {
        $authUser = auth('web')->user() ?? auth('api')->user();
        if (!$authUser) return 'received';

        return ($this->sender_id == $authUser->id && $this->sender_type == get_class($authUser))
            ? 'sent'
            : 'received';
    }

    public function getStatusInfoAttribute(): array
    {
        return [
            'status' => $this->status,
            'is_edited' => $this->is_edited,
            'read_at' => $this->read_at?->format('Y-m-d H:i:s'),
            'delivered_at' => $this->delivered_at?->format('Y-m-d H:i:s'),
            'edited_at' => $this->edited_at?->format('Y-m-d H:i:s'),
        ];
    }

    // Status Methods
    public function markAsDelivered()
    {
        if ($this->status === 'sent') {
            $this->update([
                'status' => 'delivered',
                'delivered_at' => now()
            ]);
        }
    }

    public function markAsRead()
    {
        if ($this->status !== 'read') {
            $this->update([
                'status' => 'read',
                'read_at' => now()
            ]);
        }
    }

    // Scopes
    public function scopeUnread($query, $userId, $userType)
    {
        return $query->where('receiver_id', $userId)
            ->where('receiver_type', $userType)
            ->where('status', '!=', 'read');
    }

    public function scopeBetween($query, $user1Id, $user1Type, $user2Id, $user2Type)
    {
        return $query->where(function ($q) use ($user1Id, $user1Type, $user2Id, $user2Type) {
            $q->where('sender_id', $user1Id)->where('sender_type', $user1Type)
                ->where('receiver_id', $user2Id)->where('receiver_type', $user2Type);
        })->orWhere(function ($q) use ($user1Id, $user1Type, $user2Id, $user2Type) {
            $q->where('sender_id', $user2Id)->where('sender_type', $user2Type)
                ->where('receiver_id', $user1Id)->where('receiver_type', $user1Type);
        });
    }
}
