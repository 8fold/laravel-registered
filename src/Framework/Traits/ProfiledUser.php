<?php

namespace Eightfold\Registered\Framework\Traits;

use Eightfold\Registered\Profile\UserProfile;

trait ProfiledUser
{
    abstract public function getCanAddAvatarAttribute();

    abstract public function getCanAddSiteAddressesAttribute();

    abstract public function getCanCreateBiographyAttribute();

    public function profile()
    {
        if (is_null(UserProfile::where('user_id', $this->id)->first())) {
            UserProfile::create(['user_id' => $this->id]);
        }
        return $this->hasOne(UserProfile::class, 'user_id');
    }
}
