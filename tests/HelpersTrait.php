<?php

namespace Eightfold\Registered\Tests;

use Eightfold\Registered\Models\User;
use Eightfold\Registered\Models\UserInvitation;
use Eightfold\Registered\Models\Registration;

trait HelpersTrait
{
    public function inviteUser()
    {
        return UserInvitation::invite('someone@example.com');
    }

    public function registerUser()
    {
        $invitation = $this->inviteUser();
        return UserRegistration::registerUser('someone', 'someone@example.com', 'user', $invitation->token, $invitation->code);
    }
}
