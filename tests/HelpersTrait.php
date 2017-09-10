<?php

namespace Tests;

use Eightfold\RegistrationManagementLaravel\Models\User;
use Eightfold\RegistrationManagementLaravel\Models\UserInvitation;
use Eightfold\RegistrationManagementLaravel\Models\Registration;

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