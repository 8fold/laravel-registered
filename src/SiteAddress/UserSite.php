<?php

namespace Eightfold\Registered\SiteAddress;

use Illuminate\Database\Eloquent\Model;

use Eightfold\Conveniences\Laravel\Attributes\PublicKeyAttribute;

use Eightfold\Profiled\Models\UserProfile;

class UserSite extends Model
{
    use PublicKeyAttribute;

    protected $fillable = [
        'user_id', 'address', 'type', 'profile_id'
    ];

    static protected function publicKeySalt()
    {
        return config('profiled.key_salts.site');
    }

    static protected function publicKeyPrefix()
    {
        return 'site_';
    }

    static protected function secretSalt()
    {
        return config('profiled.key_salts.site');
    }

    static public function withKey($key)
    {
        $key = str_replace('site_', '', $key);
        return static::where('public_key', $key)->first();
    }

    public function profile()
    {
        return $this->belongsTo(UserProfile::class, 'profile_id');
    }

    public function getUserAttribute()
    {
        return $this->profile->user;
    }
}
