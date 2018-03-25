<?php

namespace Eightfold\Registered\Tests\Unit;

use Eightfold\Registered\Tests\TestCase;

use Eightfold\Registered\Tests\Stubs\User;

use Eightfold\Registered\Invitation\UserInvitation;
use Eightfold\Registered\Registration\UserRegistration;

class InvitationTest extends TestCase
{
    public function testGetInvitationWithEmailAndToken()
    {
        $invitation = $this->inviteUser('something@example.com');
        $followUp = UserInvitation::withEmail($invitation->email)
            ->withToken($invitation->token)
            ->withCode($invitation->code)
            ->first();
        $this->assertNotNull($followUp, $followUp);
    }

    public function testSaveBaseInvitation()
    {
        $this->inviteUser('something@example.com');
        $invitations = UserInvitation::all();
        $this->assertTrue(count($invitations) == 1);
    }

    public function testClaimInvitation()
    {
        $claimed = $this->registerUser();
        $this->assertNotNull($claimed, $claimed);
        $this->assertTrue(is_a($claimed, UserRegistration::class));
    }
}
