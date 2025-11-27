<?php

namespace App\Mail;

use App\Models\GuestUser;
use App\Models\Chat;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewChatNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $guest;
    public $chat;

    public function __construct(GuestUser $guest, Chat $chat)
    {
        $this->guest = $guest;
        $this->chat = $chat;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Chat Message',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.chat.new_chat_notification',
            with: [
                'guestName' => $this->guest->name,
                'guestEmail' => $this->guest->email,
                'guestPhone' => $this->guest->phone,
                'msg' => $this->chat->text,
                'chatUrl' => route('chat.index'),
            ]
        );
    }
}
