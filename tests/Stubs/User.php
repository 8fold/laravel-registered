<?php

namespace Eightfold\Registered\Tests\Stubs;

use Auth;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Eightfold\Traits\PublicKeyable;

use Eightfold\Registered\Traits\RegisteredUser;
use Eightfold\Registered\Traits\RegisteredUserCapabilities;

class User extends Authenticatable
{
    use PublicKeyable,
        RegisteredUser,
        RegisteredUserCapabilities;

    protected $fillable = [
        'username',
        'email'
    ];

    static public function testing()
    {
        dd('hello');
    }
    // protected $table = 'users';
}
