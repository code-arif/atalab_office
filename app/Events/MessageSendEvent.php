<?php

namespace App\Events;

use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MessageSendEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    // public function broadcastOn(): array
    // {
    //     return [
    //         new PrivateChannel("chat-room.{$this->data->room_id}"),
    //         new PrivateChannel("chat-receiver.{$this->data->receiver_id}"),
    //         new PrivateChannel("chat-sender.{$this->data->sender_id}")
    //     ];
    // }


    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel("chat-room.{$this->data->room_id}"),
        ];

        // Add receiver channel (could be User or GuestUser)
        if ($this->data->receiver_type === 'App\\Models\\User') {
            $channels[] = new PrivateChannel("chat-receiver.{$this->data->receiver_id}");
        }

        // Add sender channel
        if ($this->data->sender_type === 'App\\Models\\User') {
            $channels[] = new PrivateChannel("chat-sender.{$this->data->sender_id}");
        }

        return $channels;
    }

    public function broadcastWith()
    {
        return [
            'data' => $this->data
        ];
    }
}
