<?php

namespace Eightfold\Registered\Models;

use Auth;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use Eightfold\Registered\Models\UserPasswordReset;
use Eightfold\Registered\Models\UserInvitation;
use Eightfold\Registered\Models\UserEmailAddress;
use Eightfold\Registered\Models\UserType;

use Eightfold\TraitsLaravel\Relationships\BelongsToUser;
use Eightfold\TraitsLaravel\Tokenizable;

use Eightfold\Registered\Traits\EmailAddressable;
use Eightfold\Registered\Traits\Typeable;

use Mail;
use Eightfold\Registered\Mail\UserRegistered;

/**
 * @todo Batch set user type
 * @todo Make sure changing user types can update the primary user type
 */
class UserRegistration extends Model
{
    use BelongsToUser,
        Tokenizable,
        EmailAddressable,
        Typeable;

    protected $fillable = [
        'token', 'user_id', 'registered_on', 'user_type_id'
    ];

    protected $hidden = [
        'token'
    ];

    static public function invitationRequired()
    {
        return config('registered.invitation_required');
    }

    static public function isProfileArea()
    {
        $isProfileArea = false;
        if (Auth::user()) {
            $trimmedProfilePath = trim(Auth::user()->registration->profilePath, '/');
            $allSubPaths = trim(Auth::user()->registration->profilePath, '/') .'/*';
            if(is_active([$trimmedProfilePath, $allSubPaths])) {
                $isProfileArea = true;

            }
        }
        return $isProfileArea;
    }

    static public function registeredUsers()
    {
        $registeredIds = UserRegistration::all()->pluck('user_id')->toArray();
        $userClass = static::belongsToUserClassName();
        $count = 0;
        $users = null;
        foreach ($registeredIds as $id) {
            if ($count == 0) {
                $users = $userClass::where('id', $id);
                $count = 1;

            } else {
                $users->orWhere('id', $id);

            }
        }
        return $users->get();
    }

    static public function registerUser(string $username, string $email, UserType $type, UserInvitation $invitation = null): UserRegistration
    {
        // Invitation required, passed invitation is null; therefore, bail.
        if (static::invitationRequired() && is_null($invitation)) {
            return null;
        }

        // Claim the invitatioin or bail.
        if (static::invitationRequired() && is_null(UserInvitation::claim($email, $invitation->token, $invitation->code))) {
            return null;
        }

        // Create user.
        $userClass = static::belongsToUserClassName();
        $user = $userClass::create([
                'username' => $username,
                'email' => $email
            ]);

        // Create registration.
        $registration = static::create([
            'token' => self::generateToken(36),
            'user_id' => $user->id,
            'user_type_id' => $type->id,
            'registered_on' => Carbon::now()
        ]);
        $registration->addEmail($email, true);
        $registration->save();

        // Link invitation to registration.
        if (!is_null($invitation)) {
            $invitation->user_registration_id = $registration->id;
            $invitation->save();
        }

        // Email new user to establish password.
        if (!\App::runningUnitTests()) {
            Mail::to($registration->defaultEmailString)
                ->send(new UserRegistered($registration));
        }

        // Pass back the registration.
        return $registration;
    }

    // TODO: Should be a different way to do this.
    // Call from RegisterController - want to maintain this capability
    // without having to know the user object.
    static public function usernameValidation(): string
    {
        return 'required|alpha_num|max:255|unique:users';
    }

    public function sentInvitations()
    {
        return $this->hasMany(UserInvitation::class, 'inviter_registration_id');
    }

    public function getUnclaimedInvitationsAttribute()
    {
        return $this->sentInvitations()->where('claimed_on', null)->get();
    }

    public function getClaimedInvitationsAttribute()
    {
        return $this->sentInvitations()->where('claimed_on', '<>', null)->get();
    }

    /**
     * Get the invitation sent to the user to register.
     *
     * @return UserInvitation [description]
     */
    public function invitation()
    {
        return $this->hasOne(UserInvitation::class);
    }


    public function passwordReset()
    {
        return $this->hasOne(UserPasswordReset::class, 'user_registration_id');
    }

    /**
     *
     * @return Collection Collection of EmailAddress objects.
     *
     */
    public function emails()
    {
        return $this->hasMany(UserEmailAddress::class, 'user_registration_id');
    }

    public function type()
    {
        return $this->belongsTo(UserType::class, 'primary_user_type_id');
    }

    public function types()
    {
        return $this->belongsToMany(UserType::class);
    }

    public function getDisplayNameAttribute()
    {
        if (strlen($this->first_name) > 0 && strlen($this->last_name) > 0) {
            return $this->first_name .' '. $this->last_name;

        } elseif (strlen($this->first_name) > 0) {
            return $this->first_name;

        } elseif (strlen($this->last_name) > 0) {
            return $this->last_name;

        }
        return $this->user->username;
    }

    public function getConfirmUrlAttribute()
    {
        return $this->profilePath .'/confirm?token='. $this->token;
    }

    public function getSetPasswordUrlAttribute()
    {
        return $this->profilePath .'/set-password?token='. $this->token;
    }

    public function getProfilePathAttribute()
    {
        return '/'. $this->type->slug .'/'. $this->user->username;
    }

    public function getEditProfilePathAttribute()
    {
        return $this->profilePath .'/edit';
    }

    public function getLinkAttribute()
    {
        return '<a href="'.
            $this->profilePath .'">'.
            $this->displayNameOrUsername .
            '</a>';
    }

    public function getEditAccountPathAttribute()
    {
        return $this->profilePath .'/account';
    }

    /** Scopes */
    public function scopeWithType(Builder $query, string $typeSlug): Builder
    {
        return $query->whereHas('types', function ($query) use ($typeSlug) {
            $query->where('slug', $typeSlug);
        });
    }

    public function scopeWithUsername(Builder $query, string $username): Builder
    {
        return $query->whereHas('user', function ($query) use ($username) {
            $query->where('username', $username);
        });
    }
}
