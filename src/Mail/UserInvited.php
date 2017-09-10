<?php

namespace Eightfold\RegistrationManagementLaravel\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use Eightfold\RegistrationManagementLaravel\Models\UserInvitation;

class UserInvited extends Mailable
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
        return $this->view('registered::workflow-invitation.email-invitation');
    }
}
