<?php

namespace App\Mail;

use App\Models\SearchAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SearchAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly SearchAlert $alert,
        public readonly Collection  $properties,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔔 ' . $this->properties->count() . ' nouveau(x) bien(s) pour : ' . $this->alert->name,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.search-alert');
    }
}
