<?php

namespace Eightfold\Registered\Invitation;

use Illuminate\Mail\Mailable;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use Eightfold\Registered\Invitation\UserInvitation;

class InvitedMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $invitation = null;

    public $invitationUrl = '';
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(UserInvitation $invitation)
    {
        $this->invitation = $invitation;
        $this->invitationUrl = env('APP_DOMAIN') .'/register/?token='. $invitation->token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('invitation::invitation-email');
    }
}
