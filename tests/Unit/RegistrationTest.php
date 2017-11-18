<?php

namespace Eightfold\Registered\Tests\Unit;

use Eightfold\Registered\Tests\TestCase;

use DB;

use Eightfold\Registered\Tests\Stubs\User;

use Eightfold\Registered\Models\UserType;
use Eightfold\Registered\Models\UserInvitation;
use Eightfold\Registered\Registration\UserRegistration;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Collection;

class RegistrationTest extends TestCase
{
    public function testUserClassName()
    {
        $expected = 'Eightfold\Registered\Tests\Stubs\User';
        $result = config('auth.providers.users.model');
        $this->assertTrue($expected == $result);
    }

    public function testRegistrationUserClassName()
    {
        $registration = $this->registerUser();
        $user = $registration->user;
        $this->assertNotNull(is_a($user, User::class));
        $this->assertTrue(is_a($user->registration, UserRegistration::class));
    }

    public function testHasUsernameAttribute()
    {
        $registration = $this->registerUser();
        $expected = 'someone';
        $result = $registration->username;
        $this->assertTrue($expected == $result, $registration->username);
    }

    public function testRegistrationConfirmUrl()
    {
        $registration = $this->registerUser();
        $this->assertNotNull($registration);
        $expect = '/owners/someone/confirm?token='. $registration->token;
        $result = $registration->confirmUrl;
        $isSame = ($expect == $result);
        $this->assertTrue($isSame, "result: ". $result ."\nexpect: ". $expect);
    }

    public function testDisplayNameUsernameOnly()
    {
        $registration = $this->registerUser();
        $this->assertNotNull($registration);
        $expected = 'someone';
        $result = $registration->getDisplayNameAttribute();
        $this->assertTrue($expected == $result);
    }

    public function testDisplayNameFirstAndLastName()
    {
        $registration = $this->registerUser();
        $registration->first_name = 'someone';
        $registration->last_name = 'else';
        $registration->save();
        $result = $registration->getDisplayNameAttribute();
        $this->assertTrue('someone else' == $result);
    }

    public function testRegistrationHasEmail()
    {
        $registration = $this->registerUser();
        $this->assertTrue($registration->emails->count() > 0);
        $this->assertTrue($registration->emails->first()->email == 'someone@example.com');
    }

    public function testCanGetRegistrationFromEmail()
    {
        $expected = $this->registerUser();
        $result = UserRegistration::withEmail('someone@example.com')->first();
        $this->assertTrue($expected->id == $result->id);
    }

    public function testCanGetDefaultEmail()
    {
        $registration = $this->registerUser();
        $expected = 'someone@example.com';
        $result = $registration->defaultEmail->email;
        $this->assertTrue($expected == $result);
    }

    public function testCanAddEmailAddress()
    {
        $registration = $this->registerUser();
        $expected = 2;
        $registration->addEmail('someoneelse@example.com');
        $registration->save();
        $this->assertTrue($registration->emails->count() == 2);
    }

    public function testCanChangeDefaultEmailAddress()
    {
        $registration = $this->registerUser();
        $this->assertTrue($registration->defaultEmail->email == 'someone@example.com');

        $expected = 'hello@example.com';
        $registration->defaultEmail = $expected;
        $registration->save();
        $result = $registration->defaultEmail->email;
        $this->assertTrue($expected == $result);
    }

    public function testCannotDeleteLastEmailAddress()
    {
        $registration = $this->registerUser();
        $registration->emailWithAddress('someone@example.com')->delete();
        $this->assertTrue($registration->emails->count() == 1);
    }

    public function testCanDeleteEmailAddress()
    {
        $registration = $this->registerUser();
        $registration->addEmail('testing@example.com');
        $this->assertTrue($registration->emails()->count() == 2);

        $registration->deleteEmail('testing@example.com');
        $this->assertTrue($registration->emails()->count() == 1);
    }

    public function testTypesIsProperReturnType()
    {
        $registration = $this->registerUser();
        $types = $registration->types;
        $this->assertTrue(is_a($types, Collection::class), get_class($types));
    }

    public function testApplicationMustHaveOwnerType()
    {
        $registration = $this->registerUser();

        $this->assertTrue($registration->primaryType->slug == 'owners');
        $this->assertTrue(UserRegistration::all()->count() == 1);

        $registration->primaryType = UserType::withSlug('users')->first();
        $this->assertTrue($registration->primaryType->slug == 'users', $registration->primaryType->slug);
        $this->assertTrue($registration->types()->count() == 2);
        $this->assertTrue($registration->types()->withSlug('owners')->count() == 1);
    }

    public function testCanChangePrimaryUserTypeWhenMultipleUsersPresent()
    {
        $registration = $this->registerUser();
        $registration2 = $this->registerUser('other', 'other@example.com');

        $this->assertTrue($registration->primaryType->slug == 'owners');
        $this->assertTrue($registration2->primaryType->slug == 'owners');
        $this->assertTrue(UserRegistration::all()->count() == 2);

        $registration->primaryType = UserType::withSlug('users')->first();
        $registration->save();
        $this->assertTrue($registration->types->count() == 2);
        $this->assertTrue($registration->primaryType->slug == 'users', $registration->primaryType->slug);
    }

    public function testCanSetMultipleTypes()
    {
        $this->seedUserTypes();
        $registration = $this->registerUser();
        $types = UserType::withSlugs(['users', 'new-types'])->get();
        $registration->types = $types;
        $registration->save();
        $this->assertTrue($registration->types()->count() == 3);
        $this->assertTrue($registration->hasType('users'));
        $this->assertTrue($registration->hasType('owners'));
        $this->assertTrue($registration->hasType('new-types'));
        $this->assertFalse($registration->hasType('newer-types'));
    }
}
