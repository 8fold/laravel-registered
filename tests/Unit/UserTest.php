<?php

namespace Tests\Unit;

use Tests\TestCase;
// use Tests\HelpersTrait;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Eightfold\RegistrationManagementLaravel\Models\User;
use Eightfold\RegistrationManagementLaravel\Models\UserInvitation;
use Eightfold\RegistrationManagementLaravel\Models\UserRegistration;

class UserTest extends TestCase
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

    public function testUserRelationships()
    {
        $this->registerUser();
        $user = User::first();
        $this->assertNotNull($user);

        $registration = $user->registration;
        $this->assertTrue(is_a($registration, UserRegistration::class));

        $invitation = $user->registration->invitation;
        $this->assertTrue(is_a($invitation, UserInvitation::class));
    }

    public function testUserDefaultEmailAddress()
    {
        $this->registerUser();
        $user = User::first();
        $registration = $user->registration;
        $this->assertTrue($registration->defaultEmailString == 'someone@example.com');
    }

    public function testChangeDefaultEmailAddress()
    {
        $this->registerUser();
        $user = User::first();
        $registration = $user->registration;
        $registration->addEmail('something@example.com', true);
        $this->assertTrue($registration->defaultEmailString == 'something@example.com');
    }

    public function testInviteAnotherUser()
    {
        $this->registerUser();
        $user = User::first();
        $invitation = UserInvitation::invite('some@example.com', 'user', $user);
        $this->assertTrue(is_a($invitation, UserInvitation::class));
        $this->assertNotNull($invitation->sender);
    }
}