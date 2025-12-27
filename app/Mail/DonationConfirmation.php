<?php

namespace App\Mail;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DonationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $donation;

    // Store data directly instead of relying on relationships
    public $participantName;
    public $userEmail;
    public $weekNumber;
    public $donorId;
    public $donationAmount;
    public $donationDate;

    /**
     * Create a new message instance.
     */
    public function __construct(Donation $donation)
    {
        $this->donation = $donation;

        // Load relationships immediately and store data
        $donation->load(['user', 'weeklyDraw']);

        // Extract data before queuing to avoid serialization issues
        $this->participantName = $donation->user->name ?? 'Valued Participant';
        $this->userEmail = $donation->user->email;
        $this->weekNumber = $donation->weeklyDraw->week_number ?? 'N/A';
        $this->donorId = $donation->user->donor_id;
        $this->donationAmount = number_format($donation->amount, 2);
        $this->donationDate = $donation->donated_at->format('F j, Y');
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Thank you for your gift to The Dignity Draw')
            ->view('emails.donation.donation-confirmation')
            ->with([
                'participantName' => $this->participantName,
                'donationAmount' => $this->donationAmount,
                'donationDate' => $this->donationDate,
                'weekNumber' => $this->weekNumber,
                'transactionId' => $this->donorId,
                'currentYear' => date('Y'),
            ]);
    }
}
