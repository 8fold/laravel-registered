<?php

namespace Eightfold\Registered\Models;

use App;

use Auth;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Eightfold\Registered\Models\UserPasswordReset;
use Eightfold\Registered\Models\UserInvitation;
use Eightfold\Registered\Models\UserEmailAddress;
use Eightfold\Registered\Models\UserType;

use Eightfold\Traits\Relationships\BelongsToUser;
use Eightfold\Traits\Tokenizable;

use Eightfold\Registered\Traits\Typeable;

use Mail;
use Eightfold\Registered\Mail\UserRegistered;

/**
 * @todo Batch set user type
 * @todo Make sure changing user types can update the primary user type
 */
class UserRegistration extends Model
{
    use Tokenizable,
        Typeable;

    protected $fillable = [
        'token', 'user_id', 'registered_on', 'user_type_id'
    ];

    protected $hidden = [
        'token'
    ];

    static public function invitationRequired(): bool
    {
        return config('registered.invitation_required');
    }

    static public function isProfileArea(): bool
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

    static public function registeredUsers(): UserRegistration
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

    static public function registerUser(string $username, string $email, UserType $type = null, UserInvitation $invitation = null): UserRegistration
    {
        $noTypeOrInvite = (is_null($type) && is_null($invitation));
        $inviteRequiredAndCouldNotclaim = (static::invitationRequired() && is_null(UserInvitation::claim($email, $invitation->token, $invitation->code)));
        if ($noTypeOrInvite && $inviteRequiredAndCouldNotclaim) {
            return null;
        }

        $user = static::creatUser($username, $email);
        $registration = static::createRegistration($user, $email);

        // Update user type.
        $setType = '';
        if (is_null($type)) {
            $setType = (is_null($invitation->user_type_id))
                ? 2
                : $invitation->user_type_id;

        } elseif (is_null($invitation)) {
            $setType = (is_null($type->id))
                ? 2
                : $type->id;

        }
        $setType = UserType::find($setType)->slug;
        $registration->type = $setType;
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

    static private function creatUser(string $username, string $email)
    {
        $user = null;
        if (App::runningUnitTests()) {
            // TODO: There has to be a way to get the user class with this check.
            \DB::table('users')->insert([
                'username' => $username,
                'email' => $email
            ]);
            $user = \DB::table('users')
                ->where('username', $username)
                ->first();

        } else {
            $userClass = static::belongsToUserClassName();
            $user = $userClass::create([
                    'username' => $username,
                    'email' => $email
                ]);

        }
        return $user;
    }

    static private function createRegistration($user, $email)
    {
        $registration = static::create([
            'token' => self::generateToken(36),
            'user_id' => $user->id,
            'registered_on' => Carbon::now()
        ]);
        $registration->addEmail($email, true);
        $registration->save();
        return $registration;
    }

    // TODO: Should be a different way to do this.
    // Call from RegisterController - want to maintain this capability
    // without having to know the user object.
    static public function usernameValidation(): string
    {
        return 'required|alpha_num|max:255|unique:users';
    }

    public function getUsernameAttribute(): string
    {
        if (App::runningUnitTests()) {
            // TODO: There has to be a way to get the user class with this check.
            $user = \DB::table('users')->where('id', $this->user_id)->first();
            return $user->username;
        }
        return $this->user->username;
    }

    public function sentInvitations(): UserInvitation
    {
        return $this->hasMany(UserInvitation::class, 'inviter_registration_id');
    }

    public function getUnclaimedInvitationsAttribute(): UserInvitation
    {
        return $this->sentInvitations()->where('claimed_on', null)->get();
    }

    public function getClaimedInvitationsAttribute(): UserInvitation
    {
        return $this->sentInvitations()->where('claimed_on', '<>', null)->get();
    }

    /**
     * Get the invitation sent to the user to register.
     *
     * @return UserInvitation [description]
     */
    public function invitation(): UserInvitation
    {
        return $this->hasOne(UserInvitation::class);
    }


    public function passwordReset(): UserPasswordReset
    {
        return $this->hasOne(UserPasswordReset::class, 'user_registration_id');
    }

    /**
     *
     * @return Collection Collection of EmailAddress objects.
     *
     */
    public function emails(): HasMany
    {
        return $this->hasMany(UserEmailAddress::class, 'user_registration_id');
    }

    /** Types */
    public function type(): BelongsTo
    {
        return $this->belongsTo(UserType::class, 'primary_user_type_id');
    }

    /**
     * Set the primary user type associated with the rgistration
     *
     * $user->registration->type = '{user-type-slug}'
     *
     * @param string $type [description]
     */
    public function setTypeAttribute(string $type)
    {
        if ($type = UserType::withSlug($type)->first()) {
            $this->type()->associate($type);

            $otherTypes = $this->scopes()->get()->pluck('slug')->toArray();
            $this->scopes = array_merge([$type->slug], $otherTypes);

            return true;
        }
        return false;
    }

    public function scopes(): BelongsToMany
    {
        return $this->belongsToMany(UserType::class, 'user_registration_user_type', 'user_type_id', 'user_registration_id');
    }

    /**
     * $user->registration->types = ['{user-type-slug}', '{user-type-slug}']
     *
     * @param array $typeSlugs [description]
     */
    public function setScopesAttribute(array $typeSlugs = [])
    {
        if (count($typeSlugs) > 0) {
            $primaryTypeId = $this->type->id;
            $currentTypeIds = UserType::withSlugs($typeSlugs)->pluck('id')->toArray();
            $merged = array_unique(array_merge([$primaryTypeId], $currentTypeIds));
dump($typeSlugs);
dump($merged);
            $this->scopes()->sync($merged);
// dd($this);
            // foreach ($this->types as $type) {
            //     $this->types()->detach($type);
            // }

            // foreach ($targetTypeIds as $id) {
            //     $type = UserType::find($id)->first();
            //     $this->types()->attach($type);
            // }

            // if (UserRegistration::withType('owners')->count() == 0) {
            //     $ownerType = UserType::withSlug('owners')->first();
            //     $this->types()->attach($ownerType->id);

            // }
            return true;
        }
        return false;
    }

    // public function updateTypes(string $primaryTypeSlug = '', array $otherTypeSlugs = [])
    // {
    //     $this->type = $primaryTypeSlug;

    //     $merge = array_merge([$primaryTypeSlug], $otherTypeSlugs);
    //     $this->types = array_unique($merge, SORT_REGULAR);
    //     return true;
    // }

    public function getSelectedTypesAttribute(): array
    {
        return $this->types()->pluck('slug');
    }

    public function getDisplayNameAttribute(): string
    {
        $string = [];
        if (strlen($this->first_name) > 0) {
            $string[] = $this->first_name;
        }

        if (strlen($this->last_name) > 0) {
            $string[] = $this->last_name;
        }

        $string = implode(' ', $string);
        if (strlen($string) < 2) {
            $string = $this->username;
        }
        return $string;
    }

    public function getConfirmUrlAttribute(): string
    {
        return $this->profilePath .'/confirm?token='. $this->token;
    }

    public function getSetPasswordUrlAttribute(): string
    {
        return $this->profilePath .'/set-password?token='. $this->token;
    }

    public function getProfilePathAttribute(): string
    {
        return '/'. $this->type->slug .'/'. $this->username;
    }

    public function getEditProfilePathAttribute(): string
    {
        return $this->profilePath .'/edit';
    }

    public function getLinkAttribute(): string
    {
        return '<a href="'.
            $this->profilePath .'">'.
            $this->displayName .
            '</a>';
    }

    public function getEditAccountPathAttribute(): string
    {
        return $this->profilePath .'/account';
    }

    public function getDefaultEmailAttribute(): ?UserEmailAddress
    {
        return $this->emails()->where('is_default', true)->first();
    }

    /**
     * @todo Deprecate ??
     *
     * @return String The default email addres of the user
     *
     */
    public function getDefaultEmailStringAttribute(): string
    {
        return optional($this->defaultEmail)->email;
    }

    public function addEmail(string $email, $isDefault = false): UserEmailAddress
    {
        if ($isDefault && $default = $this->defaultEmail) {
            if ($email == $default->email) {
                return $default;
            }
            $default->is_default = false;
            $default->save();
        }
        return UserEmailAddress::create([
                'email' => $email,
                'is_default' => $isDefault,
                'user_registration_id' => $this->id
            ]);
    }

    public function setDefaultEmailAttribute(string $email): ?UserEmailAddress
    {
        // Check for default change.
        if ($currentDefault = $this->defaultEmail) {
            if ($this->defaultEmailString !== $email) {
                $currentDefault->is_default = false;
                $currentDefault->save();

            } elseif ($this->defaultEmailString == $email) {
                return null;

            }
        }
        $address = UserEmailAddress::withAddress($email)->first();
        if (is_null($address)) {
            UserEmailAddress::validator($email)->validate();
            $address = $this->addEmail($email);
        }
        $address->is_default = true;
        $address->save();
        return $address;
    }

    public function emailWithAddress(string $address): UserEmailAddress
    {
        return $this->emails()->withAddress($address)->first();
    }

    public function deleteEmail(string $address): bool
    {
        return $this->emailWithAddress($address)->delete();
    }

    /** Scopes */
    public function scopeWithEmail(Builder $query, string $address): Builder
    {
        return $query->whereHas('emails', function ($query) use ($address) {
            $query->where('email', $address);
        });
    }

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
