<?php

namespace Eightfold\RegistrationManagementLaravel\Models;

use Illuminate\Database\Eloquent\Model;

use Eightfold\TraitsLaravel\Tokenizable;

use Eightfold\RegistrationManagementLaravel\Traits\BelongsToUserRegistration;

class UserPasswordReset extends Model
{
    use Tokenizable,
        BelongsToUserRegistration;

    protected $fillable = [
        'token', 'code', 'user_registration_id'
    ];
}
