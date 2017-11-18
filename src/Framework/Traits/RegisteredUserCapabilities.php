<?php

namespace Eightfold\Registered\Framework\Traits;

trait RegisteredUserCapabilities
{
    public function getCanManageUsersAttribute()
    {
        return $this->isSiteOwner;
    }

    public function getCanInviteUsersAttribute()
    {
        return $this->isSiteOwner;
    }

    public function getCanChangeUserTypesAttribute()
    {
        return $this->isSiteOwner;
    }
}
