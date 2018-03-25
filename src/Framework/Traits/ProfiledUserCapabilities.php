<?php

namespace Eightfold\Registered\Framework\Traits;

use Eightfold\Registered\Profile\UserProfile;

trait ProfiledUserCapabilities
{
    abstract public function getCanAddAvatarAttribute();

    abstract public function getCanAddSiteAddressesAttribute();

    abstract public function getCanCreateBiographyAttribute();
}
