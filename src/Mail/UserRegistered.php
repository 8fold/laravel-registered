<?php

namespace Eightfold\RegistrationManagementLaravel\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserRegistered extends Mailable
{
    use Queueable, SerializesModels;

    public $confirmUrl = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($registration)
    {
        $this->confirmUrl = $registration->confirmUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {            
        return $this->view('registered::workflow-registration.email-registered');
    }
}
