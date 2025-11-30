<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Invitation $invitation;
    public bool $isNewUser;
    public string $inviteUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Invitation $invitation, bool $isNewUser)
    {
        $this->invitation = $invitation;
        $this->isNewUser = $isNewUser;
        
        // Construir URL baseada no tipo de usuário
        if ($isNewUser) {
            $this->inviteUrl = url("/invite/register/{$invitation->token}");
        } else {
            $this->inviteUrl = url("/invite/accept/{$invitation->token}");
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isNewUser 
            ? 'Convite para se cadastrar na plataforma'
            : 'Convite para acessar a plataforma';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation',
            with: [
                'invitation' => $this->invitation,
                'isNewUser' => $this->isNewUser,
                'inviteUrl' => $this->inviteUrl,
                'platform' => $this->invitation->platform,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

