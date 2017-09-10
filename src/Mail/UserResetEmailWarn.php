<?php

namespace Eightfold\RegistrationManagementLaravel\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserResetEmailWarn extends Mailable
{
    use Queueable, SerializesModels;

    public $user = null;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {            
        return $this->view('registered::workflow-forgot.email-reset-warn');
    }
}
