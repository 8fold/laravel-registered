<?php

namespace Eightfold\Registered\Tests\Unit;

use Eightfold\Registered\Tests\TestCase;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Eightfold\Registered\Models\User;
use Eightfold\Registered\Models\UserInvitation;
use Eightfold\Registered\Models\UserRegistration;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function inviteUser()
    {
        return UserInvitation::invite('someone@example.com');
    }

    public function registerUser()
    {
        $invitation = $this->inviteUser();
        return UserRegistration::registerUser('someone', 'someone@example.com', 'user', $invitation->token, $invitation->code);
    }

    public function testRegistrationConfirmUrl()
    {
        $registration = $this->registerUser();
        $expect = '/users/someone/confirm?token='. $registration->token;
        $result = $registration->confirmUrl;
        $isSame = ($expect == $result);
        $this->assertTrue($isSame, "result: ". $result ."\nexpect: ". $expect);
    }
}
