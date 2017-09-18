<?php

namespace Eightfold\Registered\Traits;

use Hash;
use Validator;

use Eightfold\Registered\Models\UserRegistration;

use Eightfold\Registered\Traits\Usernameable;

trait RegisteredUser
{
    use Usernameable;

    public function getUserTypeSlugAttribute()
    {
        return $this->registration->type->slug;
    }

    public function registration()
    {
        return $this->hasOne(UserRegistration::class, 'user_id');
    }

    public function getIsSiteOwnerAttribute()
    {
        return $this->userTypeSlug == 'owners';
    }

    public function isSiteOwner($string = false)
    {
        // Only people strictly assigned as Owner will be.
        return $this->isSiteOwner;
    }

    public function isUser($strict = false)
    {
        $s = ($this->userTypeSlug == 'users');
        if ($strict) {
            return $s;
        }
        // All user types are users.
        return true;
    }

    public function getIsUserAttribute()
    {
        return $this->isUser();
    }

    public function setPasswordAttribute($pass)
    {
        $this->attributes['password'] = Hash::make($pass);
        $this->save();
    }
}
