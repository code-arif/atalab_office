<?php
// app/Events/DonationCreated.php

namespace App\Events;

use App\Models\Donation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DonationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Donation $donation) {}

    public function broadcastOn(): Channel
    {
        return new Channel('donations');
    }

    // public function broadcastAs(): string
    // {
    //     return 'donation.created';
    // }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->donation->id,
            'user_name' => $this->donation->user ? $this->donation->user->name : 'Anonymous',
            'amount' => $this->donation->amount,
            'created_at' => $this->donation->created_at->diffForHumans(),
            'timestamp' => $this->donation->created_at->timestamp,
        ];
    }
}
