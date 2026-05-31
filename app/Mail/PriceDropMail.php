<?php

namespace App\Mail;

use App\Models\Property;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PriceDropMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User     $user,
        public readonly Property $property,
        public readonly float    $oldPrice,
        public readonly float    $newPrice,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Baisse de prix : ' . $this->property->title,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.price-drop');
    }
}
