<?php

namespace Eightfold\Registered\Models;

use Illuminate\Database\Eloquent\Model;

use Eightfold\TraitsLaravel\Tokenizable;

use Eightfold\Registered\Traits\BelongsToUserRegistration;

class UserPasswordReset extends Model
{
    use Tokenizable,
        BelongsToUserRegistration;

    protected $fillable = [
        'token', 'code', 'user_registration_id'
    ];
}
