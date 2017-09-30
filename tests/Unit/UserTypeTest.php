<?php

namespace Eightfold\Registered\Tests\Unit;

use Eightfold\Registered\Tests\TestCase;

use Eightfold\Registered\Tests\Stubs\User;

use Eightfold\Registered\Models\UserType;

class UserTypeTest extends TestCase
{
    public function testThereAreTwoUserTypes()
    {
        $this->assertTrue(UserType::all()->count() == 2);
    }
    public function testWithSlugsReturnsArrayOfUserTypes()
    {
        $withSlugs = UserType::withSlugs(['owners', 'users']);
        $this->assertTrue($withSlugs->count() == 2);

        $withSlugs = UserType::withSlugs(['owners']);
        $this->assertTrue($withSlugs->count() == 1);

        $this->assertTrue(is_a($withSlugs->first(), UserType::class));
    }

    public function testWithSlugsCanReturnPluckedFieldsOfUserTypes()
    {
        $withSlugs = UserType::withSlugs(['owners', 'users'])->pluck('id')->toArray();
        $this->assertTrue(is_array($withSlugs), $withSlugs);
    }

    public function testCanCreateMoreTypes()
    {
        $this->assertTrue(UserType::all()->count() == 2);
        $this->seedUserTypes();
        $this->assertTrue(UserType::all()->count() == 4);
    }
}
