<?php

namespace Eightfold\Registered\Profile;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\File;

use Eightfold\Conveniences\Laravel\UserRelationships\BelongsToUser;

use Eightfold\Registered\SiteAddress\UserSite;

use Eightfold\Html\Html;
use Eightfold\UIKit\UIKit;

class UserProfile extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id', 'biography', 'photo_path', 'first_name', 'last_name'
    ];

    public function sites()
    {
        return $this->hasMany(UserSite::class, 'profile_id');
    }

    public function sitesWithType($type)
    {
        return $this->sites()->where('type', $type)->get();
    }

    public function addSiteOfType($address, $type)
    {
        return UserSite::create([
                'profile_id' => $this->id,
                'type' => $type,
                'address' => $address
            ]);
    }

    public function setAvatarAttribute($path)
    {
        $this->photo_path = $path;
        $this->save();
        return true;
    }

    public function getAvatarBasePathAttribute()
    {
        return '/user_images/'. $this->user->public_key;
    }

    public function getAvatarAttribute()
    {
        if (strlen($this->photo_path) > 0) {
            return $this->avatarBasePath .'/'. $this->photo_path;
        }
        return null;
    }

    public function getAvatarFigureAttribute()
    {
        dd('here');
        $avatar = $this->avatar;
        $exists = (!is_null($avatar) && file_exists(public_path($avatar)));
        return [
            'element' => 'figure',
            'attributes' => [
                'class' => 'ef-radial-figure'
            ],
            'content' => [
                [
                    'element' => 'img',
                    'attributes' => [
                        'src' => ($exists)
                            ? url(substr($avatar, 1))
                            : url('img/logo-jewel.svg'),
                        'alt' => ($exists)
                            ? 'Profile picture of '. $this->user->registration->displayName
                            : 'Placeholder for profile picture of '. $this->user->registration->displayName
                    ]
                ]
            ]
        ];
    }

    public function deleteAvatar()
    {
        File::delete(public_path($this->avatar));
        $this->avatar = null;
        $this->save();
    }
}
