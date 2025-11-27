<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\Room;
use App\Models\User;
use App\Models\GuestUser;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class ChatTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create admin
        $admin = User::where('role', 'admin')->first();


        // Create a test guest user
        $guest = GuestUser::create([
            'name' => 'Test Guest',
            'email' => 'guest1@example.com',
            'phone' => '1234567890',
            'session_id' => Str::uuid(),
            'ip_address' => '127.0.0.1',
            'last_activity_at' => now(),
        ]);

        // Create room between admin and guest
        $room = Room::create([
            'user_one_id' => $admin->id,
            'user_one_type' => User::class,
            'user_two_id' => $guest->id,
            'user_two_type' => GuestUser::class,
        ]);

        // Create test messages
        Chat::create([
            'sender_id' => $guest->id,
            'sender_type' => GuestUser::class,
            'receiver_id' => $admin->id,
            'receiver_type' => User::class,
            'text' => 'Hello admin! I need help.',
            'room_id' => $room->id,
            'status' => 'sent',
        ]);

        Chat::create([
            'sender_id' => $admin->id,
            'sender_type' => User::class,
            'receiver_id' => $guest->id,
            'receiver_type' => GuestUser::class,
            'text' => 'Hi! How can I help you?',
            'room_id' => $room->id,
            'status' => 'read',
        ]);

        $this->command->info('Test chat data created!');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('Guest Session ID: ' . $guest->session_id);
    }
}
