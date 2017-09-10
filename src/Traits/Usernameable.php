<?php

namespace Eightfold\RegistrationManagementLaravel\Traits;

use Validator;
use Eightfold\RegistrationManagementLaravel\Models\UserRegistration;

trait Usernameable 
{
    public function setUsernameAttribute(string $username): bool
    {
        if (static::usernameValidatorPassed($username)) {
            $this->usernameValidator($username)->validate();
            $this->attributes['username'] = strtolower($username);
            return true;
        }
        return false;
    }

    /**
     *
     * @param  string $username The username of the person you are looking for.
     * 
     * @return User
     */
    static public function withUsername(string $username)
    {
        return static::where('username', $username)->first();
    }

    static protected function usernameValidatorPassed(string $username): bool
    {
        if (static::usernameValidator($username)->fails()) {
            return false;
        }
        return true;
    }

    static protected function usernameValidator(string $username)
    {
        return Validator::make(['username' => $username], [
            'username' => static::usernameValidation()
        ]);
    }

    static public function usernameValidation(): string
    {
        return UserRegistration::usernameValidation();
    }    
}
