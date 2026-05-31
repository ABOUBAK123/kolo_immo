<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\ContractRenewal;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RenewalRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ContractRenewal $renewal,
        public readonly Booking         $booking,
        public readonly User            $recipient,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '🔄 Demande de renouvellement — ' . ($this->booking->property->title ?? ''));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.renewal-request');
    }
}
