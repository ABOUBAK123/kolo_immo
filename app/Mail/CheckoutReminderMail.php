<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckoutReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Booking $booking,
        public readonly User    $recipient,
        public readonly int     $daysLeft,
        public readonly string  $recipientRole, // 'tenant' | 'owner'
    ) {}

    public function envelope(): Envelope
    {
        $label = $this->daysLeft === 1 ? 'demain' : "dans {$this->daysLeft} jours";
        return new Envelope(
            subject: "📅 Fin de séjour {$label} — {$this->booking->property->title}"
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.checkout-reminder');
    }
}
