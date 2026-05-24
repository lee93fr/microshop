<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $invitationUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Vous avez été invité(e) sur AlcoGest');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.user-invitation');
    }
}
