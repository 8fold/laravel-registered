<?php

namespace Eightfold\Registered\Tests\Stubs;

use Auth;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Eightfold\Conveniences\Php\Tokens\PublicKeyable;

use Eightfold\Registered\Framework\Traits\RegisteredUser;
use Eightfold\Registered\Framework\Traits\RegisteredUserCapabilities;

class User extends Authenticatable
{
    use PublicKeyable,
        RegisteredUser,
        RegisteredUserCapabilities;

    protected $fillable = [
        'username',
        'email'
    ];

    static public function publicKeySalt(): string
    {
        return 'some salt';
    }

    static public function publicKeyPrefix(): string
    {
        return 'user_';
    }
}
