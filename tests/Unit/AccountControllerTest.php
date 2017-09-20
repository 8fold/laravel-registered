<?php

namespace Eightfold\Registered\Tests\Unit;

use Eightfold\Registered\Tests\TestCase;

use Illuminate\Http\Request;

use Eightfold\Registered\Controllers\AccountController;
use Eightfold\Registered\Tests\Stubs\User;

class AccountControllerTest extends TestCase
{
    public function testDidUpdateUserTypes()
    {
        $registration = $this->registerUser();
        $this->assertTrue($registration->types()->count() == 1);

        $controller = new AccountController();
        $username = 'someone';
        $request = new Request();
        $request->replace([
            'primary_type' => 'users',
            'types' => [
                'users',
                'owners'
            ]
        ]);
        $controller->updateType($request, $username);
        $user = User::first();
        $this->assertTrue($registration->primaryType == 'users');
        $this->assertTrue($registration->types()->count() == 2);
    }
}
