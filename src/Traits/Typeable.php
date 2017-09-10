<?php

namespace Eightfold\RegisteredLaravel\Traits;

use Eightfold\RegisteredLaravel\Models\UserType;
use Eightfold\RegisteredLaravel\Models\UserRegistration;

trait Typeable
{
    static public function ofTypes($types = [], $strict = false)
    {
        $userTypeIds = [];
        foreach ($types as $type) {
            $userTypes = static::getSameAsUserTypesFor($type, $strict);
            $userTypeIds = array_merge($userTypeIds, $userTypes);
        }

        $registrations = null;
        $count = 0;
        foreach ($userTypeIds as $type) {
            if ($count == 0) {
                $registrations = UserRegistration::where('user_type_id', $type);
                $count = 1;

            } else {
                $registrations->orWhere('user_type_id', $type);
            }
        }
        return $registrations->get();
    }

    static private function getSameAsUserTypesFor($type, $strict = false)
    {
        $mainType = UserType::where('slug', $type)->first();
        $mainId = [$mainType->id];
        $mainSameAs = explode(',', $mainType->same_as);
        $sameAsIds = [];
        foreach ($mainSameAs as $sameAs) {
            if (strlen($sameAs) > 0) {
                $sameAsIds[] = UserType::where('slug', $sameAs)->first()->id;
            }
        }
        $merged = array_merge($mainId, $sameAsIds);
        return $merged;
    }

    static public function convertToType($typeable, $typeSlug)
    {
        if ($userType = UserType::where('slug', $typeSlug)->first()) {
            $typeable->user_type_id = $userType->id;
            $typeable->save();
            return true;
        }
        return false;
    }

    public function type()
    {
        return $this->belongsTo(UserType::class, 'user_type_id');
    }

    public function userTypeSelectOptions()
    {
        return UserType::selectOptions();
    }

    public function getIsType($type)
    {
        $t = $this->type;
        return ($t->slug == $type);
    }
}
