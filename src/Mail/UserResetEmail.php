<?php

namespace Eightfold\RegisteredLaravel\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserResetEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user = null;

    public $reset_token = null;

    public $reset_code = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $reset_token, $reset_code)
    {
        $this->user = $user;
        $this->reset_token = $reset_token;
        $this->reset_code = $reset_code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('registered::emails.reset');
    }
}
