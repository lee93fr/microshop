<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailChangeVerification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $verificationUrl,
        public readonly string $newEmail,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Confirmez votre nouvelle adresse email — La Tournée!');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.email-change-verification');
    }
}
