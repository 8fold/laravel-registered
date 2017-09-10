<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Eightfold\RegistrationManagementLaravel\Models\User;
use Eightfold\RegistrationManagementLaravel\Models\UserInvitation;
use Eightfold\RegistrationManagementLaravel\Models\UserRegistration;

class InvitationTest extends TestCase
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

    public function testInvitationIsRequired()
    {
        $this->assertTrue(config('registration-management.invitation_required'));
    }

    public function testSaveBaseInvitation()
    {
        $this->inviteUser();
        $invitations = UserInvitation::all();
        $this->assertTrue(count($invitations) == 1);
    }

    public function testClaimInvitation()
    {
        $claimed = $this->registerUser();
        $this->assertNotNull($claimed);
        $this->assertTrue(is_a($claimed, UserRegistration::class));
    }
}