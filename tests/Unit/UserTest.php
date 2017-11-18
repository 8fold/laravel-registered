<?php

namespace Eightfold\Registered\Tests\Unit;

use Eightfold\Registered\Tests\TestCase;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Eightfold\Registered\Tests\Stubs\User;

use Eightfold\Registered\Models\UserType;
use Eightfold\Registered\Models\UserInvitation;
use Eightfold\Registered\Registration\UserRegistration;

/**
 * @todo Need to have a user model solution that works.
 */
class UserTest extends TestCase
{
    public function testUserRelationships()
    {
        $this->registerUser();
        $user = User::find(1);
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
        $user = User::first()->registration;
        $type = UserType::withSlug('users')->first();
        $invitation = UserInvitation::invite('some@example.com', $type, $user);
        $this->assertTrue(is_a($invitation, UserInvitation::class), get_class($invitation));
        $this->assertNotNull($invitation->senderRegistration);
    }
}
