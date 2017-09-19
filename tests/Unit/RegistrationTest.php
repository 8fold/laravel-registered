<?php

namespace Eightfold\Registered\Tests\Unit;

use Eightfold\Registered\Tests\TestCase;

// use Eightfold\Registered\Tests\Stubs\User;

use Eightfold\Registered\Models\UserInvitation;
use Eightfold\Registered\Models\UserRegistration;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RegistrationTest extends TestCase
{
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
        $types = $registration->types();
        $this->assertTrue(is_a($types, BelongsToMany::class), get_class($types));
    }

    public function testCanUpdateUserTypes()
    {
        $registration = $this->registerUser();
        $this->assertTrue($registration->type->slug == 'owners');
        $this->assertTrue($registration->types->count() == 1, $registration->types);

        $registration->updateTypes('users', ['users', 'owners']);
        $this->assertTrue($registration->type->slug == 'users');
        $this->assertTrue($registration->types->count() == 2);
    }
}
