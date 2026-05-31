<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RenewalReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Booking $booking,
        public readonly User    $recipient,
        public readonly int     $daysLeft,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "⏰ Fin de séjour dans {$this->daysLeft} jours — {$this->booking->property->title}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.renewal-reminder');
    }
}
