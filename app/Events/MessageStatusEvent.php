<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageStatusEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chat;

    public function __construct($chat)
    {
        $this->chat = $chat;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("chat-room.{$this->chat->room_id}"),
            new PrivateChannel("chat-sender.{$this->chat->sender_id}")
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->chat->id,
            'status' => $this->chat->status,
            'read_at' => $this->chat->read_at?->format('Y-m-d H:i:s'),
            'delivered_at' => $this->chat->delivered_at?->format('Y-m-d H:i:s'),
        ];
    }
}
