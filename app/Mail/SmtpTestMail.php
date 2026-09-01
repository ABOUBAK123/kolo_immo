<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SmtpTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $to,
        public readonly string $host,
        public readonly string $sentAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Kolo Immo — Test de configuration SMTP');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.smtp-test',
            with: [
                'to'     => $this->to,
                'host'   => $this->host,
                'sentAt' => $this->sentAt,
            ],
        );
    }
}
