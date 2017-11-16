<?php

namespace Eightfold\Registered\Traits;

use Auth;
use Hash;
use Validator;

use Eightfold\Registered\Models\UserRegistration;
use Eightfold\Registered\Models\UserType;

trait RegisteredUser
{
    public function canViewType(UserType $type): bool
    {
        return $this->isSiteOwner;
    }

    public function getUserTypeSlugAttribute()
    {
        return $this->registration->primaryType->slug;
    }

    public function registration()
    {
        return $this->hasOne(UserRegistration::class, 'user_id');
    }

    public function getIsSiteOwnerAttribute(): bool
    {
        return $this->registration->hasType('owners');
    }

    public function isMe(string $username): bool
    {
        return ($this->username == $username);
    }

    public function isMyProfile(string $username): bool
    {
        $isMyProfile = false;
        if (Auth::user() && Auth::user()->username == $username) {
            $isMyProfile = true;
        }
        return $isMyProfile;
    }

    public function isSiteOwner($string = false): bool
    {
        // Only people strictly assigned as Owner will be.
        return $this->isSiteOwner;
    }

    public function isUser($strict = false): bool
    {
        $s = ($this->userTypeSlug == 'users');
        if ($strict) {
            return $s;
        }
        // All user types are users.
        return true;
    }

    public function getIsUserAttribute(): bool
    {
        return $this->isUser();
    }

    public function setPasswordAttribute($pass)
    {
        $this->attributes['password'] = Hash::make($pass);
        $this->save();
    }

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
