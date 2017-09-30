<?php

namespace Eightfold\Registered\Models;

use App;

use Auth;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Collection;

use Eightfold\Registered\Models\UserPasswordReset;
use Eightfold\Registered\Models\UserInvitation;
use Eightfold\Registered\Models\UserEmailAddress;
use Eightfold\Registered\Models\UserType;

use Eightfold\Conveniences\Laravel\UserRelationships\BelongsToUser;
use Eightfold\Conveniences\Laravel\Attributes\TokenAttribute;

use Eightfold\Registered\Traits\Typeable;

use Mail;
use Eightfold\Registered\Mail\UserRegistered;

/**
 * @todo Batch set user type
 * @todo Make sure changing user types can update the primary user type
 */
class UserRegistration extends Model
{
    use TokenAttribute,
        BelongsToUser;

    protected $fillable = [
        'token', 'user_id', 'registered_on', 'user_type_id'
    ];

    protected $hidden = [
        'token'
    ];

    /**
     * Add a new user to the database with a registration record.
     *
     * @param  string              $username   [description]
     * @param  string              $email      [description]
     * @param  UserType|null       $type       [description]
     * @param  UserInvitation|null $invitation [description]
     * @return [type]                          [description]
     */
    static public function registerUser(string $username, string $email, UserType $type = null, UserInvitation $invitation = null): UserRegistration
    {
        $noTypeOrInvite = (is_null($type) && is_null($invitation));
        $inviteRequiredAndCouldNotclaim = (static::invitationRequired() && is_null(UserInvitation::claim($email, $invitation->token, $invitation->code)));
        if ($noTypeOrInvite && $inviteRequiredAndCouldNotclaim) {
            return null;
        }
        $userType = static::determineUserType($type, $invitation);
        $user = static::createUser($username, $email);
        $registration = static::createRegistration($user, $email);
        $registration->primaryType = $userType;
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

    static public function hasOwner()
    {
        return (static::withType('owners')->count() > 0);
    }

    /**
     * Determine user type of registering user
     *
     * @param  UserType|null       $type       [description]
     * @param  UserInvitation|null $invitation [description]
     * @return [type]                          [description]
     */
    static private function determineUserType(UserType $type = null, UserInvitation $invitation = null): UserType
    {
        $setType = '';
        if (static::all()->count() == 0) {
            $setType = UserType::withSlug('owners')->first();

        } elseif (is_null($type)) {
            $setType = (is_null($invitation->user_type_id))
                ? UserType::withSlug('users')->first()
                : UserType::find($invitation->user_type_id);

        } elseif (is_null($invitation)) {
            $setType = (is_null($type->id))
                ? UserType::withSlug('users')->first()
                : UserType::find($type->id);

        }
        return $setType;
    }

    /**
     * Create the User database record
     *
     * @param  string $username [description]
     * @param  string $email    [description]
     * @return [type]           [description]
     */
    static private function createUser(string $username, string $email)
    {
        $userClass = static::belongsToUserClassName();
        $user = $userClass::create([
                'username' => $username,
                'email' => $email
            ]);
        return $user;
    }

    /**
     * Create the registration record for the User
     *
     * @param  [type] $user  [description]
     * @param  [type] $email [description]
     * @return [type]        [description]
     */
    static private function createRegistration($user, $email): UserRegistration
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

    /**
     * Check if an invitation is required to register
     *
     * @todo Refactor to include checking number of users; if 0, return false. This
     *       logic is currently
     * @return [type] [description]
     */
    static public function invitationRequired(): bool
    {
        if (UserRegistration::withType('owners')->count() == 0) {
            return false;
        }
        return config('registered.invitations.required');
    }

    /**
     * [isProfileArea description]
     * @return boolean [description]
     */
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

    // TODO: Should be a different way to do this.
    // Call from RegisterController - want to maintain this capability
    // without having to know the user object.
    static public function usernameValidation(): string
    {
        return 'required|alpha_num|max:255|unique:users';
    }

    public function getUsernameAttribute(): string
    {
        return $this->user->username;
    }

    public function sentInvitations(): HasMany
    {
        return $this->hasMany(UserInvitation::class, 'inviter_registration_id');
    }

    public function getUnclaimedInvitationsAttribute(): Collection
    {
        return $this->sentInvitations()->where('claimed_on', null)->get();
    }

    public function getClaimedInvitationsAttribute(): Collection
    {
        return $this->sentInvitations()->where('claimed_on', '<>', null)->get();
    }

    /**
     * Get the invitation sent to the user to register.
     *
     * @return UserInvitation [description]
     */
    public function invitation(): HasOne
    {
        return $this->hasOne(UserInvitation::class);
    }

    public function passwordReset(): UserPasswordReset
    {
        return $this->hasOne(UserPasswordReset::class, 'user_registration_id');
    }

    /** Types */
    public function types(): BelongsToMany
    {
        return $this->belongsToMany(UserType::class, 'user_registration_user_type', 'user_registration_id', 'user_type_id');
    }

    public function getPrimaryTypeAttribute(): UserType
    {
        $hasPrimary = !is_null($this->types()->wherePivot('is_primary', 1)->first());
        if ($hasPrimary) {
            return $this->types()->wherePivot('is_primary', 1)->first();
        }

        // We have not set a type yet.
        // We only have one; therefore, make it the primary.
        switch ($typeCount = $this->types()->count()) {
            case ($typeCount >= 2):
                if ($owner = $this->types()->withSlug('owners')->first()) {
                    $this->types()->attach($owner->id, ['is_primary' => true]);

                } else {
                    $user = $this->types()->withSlug('users')->first();
                    $this->types()->attach($user->id, ['is_primary' => true]);
                }
                break;

            case ($typeCount == 1):
                $typeId = $this->types()->first()->id;
                $this->types()->attach($typeId, ['is_primary' => true]);
                break;

            default:
                $typeId = UserType::withSlug('users')->first();
                $this->types()->attach($typeId, ['is_primary' => true]);
                break;
        }
        $this->save();
        return $this->getPrimaryTypeAttribute();
    }

    public function setPrimaryTypeAttribute(UserType $type): UserType
    {
        // Get the ids for all my current types.
        $current = $this->types()->pluck('id')->toArray();
        $targetId = $type->id;
        foreach ($current as $currentId) {
            $this->types()->detach($currentId);
        }

        // Check for owner
        if (static::withType('owners')->count() == 0) {
            $current[] = UserType::withSlug('owners')->first()->id;

        }

        // Merge the id set
        $merged = array_unique(array_merge([$targetId], $current));
        foreach ($merged as $typeId) {
            if ($typeId == $targetId) {
                $this->types()->attach($typeId, ['is_primary' => true]);

            } else {
                $this->types()->attach($typeId);

            }
        }
        return $this->primaryType;
    }


    public function setTypesAttribute(Collection $types): bool
    {
        // Don't lose primary.
        $primary = $this->primaryType;
        $this->types()->sync($types);
        $this->primaryType = $primary;
        return true;
    }

    public function updateTypes(string $primaryType, array $typeSlugs): UserRegistration
    {
        $this->primaryType = UserType::withSlug($primaryType)->first();
        $this->primaryTypes = UserType::withSlugs($typeSlugs)->get();
        return $this;
    }

    public function hasType(string $typeSlug): bool
    {
        return ($this->types()->withSlug($typeSlug)->count() > 0);
    }

    /** Strings */
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
        return '/'. $this->primaryType->slug .'/'. $this->username;
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

    /** Email */
    /**
     *
     * @return Collection Collection of EmailAddress objects.
     *
     */
    public function emails(): HasMany
    {
        return $this->hasMany(UserEmailAddress::class, 'user_registration_id');
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
        if (is_null($this->defaultEmail) && !is_null($this->user->email)) {
            $default = UserEmailAddress::withAddress($this->user->email)->first();
            $default->is_default = true;
            $default->save();
        }
        return $this->defaultEmail->email;
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
    public function scopeWithType(Builder $query, string $typeSlug): Builder
    {
        return $query->whereHas('types', function ($query) use ($typeSlug) {
            $query->where('slug', $typeSlug);
        });
    }

    public function scopeWithTypes(Builder $query, array $typeSlugs): Builder
    {
        return $query->whereHas('types', function ($query) use ($typeSlugs) {
            $count = 0;
            foreach ($typeSlugs as $slug) {
                if ($count == 0) {
                    $query->where('slug', $slug);
                    $count++;

                } else {
                    $query->orWhere('slug', $slug);
                }
            }
            return $query;
        });

    }

    public function scopeWithEmail(Builder $query, string $address): Builder
    {
        return $query->whereHas('emails', function ($query) use ($address) {
            $query->where('email', $address);
        });
    }


}
