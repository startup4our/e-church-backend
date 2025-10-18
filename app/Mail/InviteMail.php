<?php

namespace App\Mail;

use App\Models\Invite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InviteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Invite $invite;

    /**
     * Cria uma nova instância de InviteMail
     */
    public function __construct(Invite $invite)
    {
        $this->invite = $invite;
    }

    /**
     * Monta o e-mail
     */
    public function build()
    {
        // Gera a URL de cadastro com o token do convite
        $url = env('FRONTEND_URL') . '/register?invite=' . $this->invite->token;

        return $this->subject('Convite para participar do eChurch')
            ->view('emails.invite') // aponta para resources/views/emails/invite.blade.php
            ->with([
                'invite' => $this->invite,
                'url' => $url,
            ]);
    }
}
