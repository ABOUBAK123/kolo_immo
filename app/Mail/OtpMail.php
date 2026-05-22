<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $code,
        public readonly string $purpose,
        public readonly string $recipientName = ''
    ) {}

    public function envelope(): Envelope
    {
        $subject = match($this->purpose) {
            'phone_verify'   => 'Kolo Immo — Code de vérification',
            'login'          => 'Kolo Immo — Code de connexion',
            'password_reset' => 'Kolo Immo — Réinitialisation de mot de passe',
            'contract_sign'  => 'Kolo Immo — Code de signature de contrat',
            default          => 'Kolo Immo — Votre code de sécurité',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.otp');
    }

    public function attachments(): array
    {
        return [];
    }
}
