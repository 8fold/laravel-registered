<?php

namespace Eightfold\Registered\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Carbon\Carbon;

use Mail;

use Eightfold\Registered\Mail\UserInvited;

use Eightfold\Registered\Models\UserInvitation;
use Eightfold\Registered\Models\UserInvitationRequest;
use Eightfold\Registered\Models\UserRegistration;
use Eightfold\Registered\Models\UserType;

use Eightfold\Traits\PublicKeyable;
use Eightfold\Registered\Traits\Typeable;
use Eightfold\Registered\Traits\BelongsToUserRegistration;

class UserInvitation extends Model
{
    use PublicKeyable,
        Typeable,
        BelongsToUserRegistration;

    protected $fillable = [
        'email', 'type', 'token', 'code', 'inviter_registration_id', 'user_type_id'
    ];

    protected $dates = [
        'claimed_on'
    ];

    protected $hidden = [
        'token', 'code'
    ];

    static protected function publicKeySalt(): string
    {
        return config('registered.key_salts.invitation');
    }

    static protected function publicKeyPrefix(): string
    {
        return 'invite_';
    }

    static public function unclaimed()
    {
        return UserInvitation::where('claimed_on', null)->get();
    }

    static public function claimed()
    {
        return UserInvitation::where('claimed_on', '<>', null)->get();
    }

    static public function invite(string $email, UserType $type = null, UserRegistration $sender = null): UserInvitation
    {
        if (is_null($type)) {
            $type = UserType::find(1);
        }

        $invitation = static::withEmail($email)
            ->withType($type)
            ->withSender($sender)
            ->first();
        if (is_null($invitation)) {
            $invitation = static::create([
                    'email' => $email,
                    'user_type_id' => $type->id,
                    'token' => str_random(36),
                    'code' => str_random(10),
                    'inviter_registration_id' => (is_null($sender))
                        ? 1
                        : $sender->id
                ]);

        } else {
            $invitation->updated_at = Carbon::now();
            $invitation->save();

        }

        if ($request = UserInvitationRequest::withEmail($email)->first()) {
            $request->user_invitation_id = $invitation->id;
            $request->save();

        }

        // TODO: Workflow this.
        if (!\App::runningUnitTests()) {
            Mail::to($invitation->email)->send(new UserInvited($invitation));
        }
        return $invitation;
    }

    static public function claim($email, $token, $code): UserInvitation
    {
        $invitation = UserInvitation::withEmail($email)
            ->withToken($token)
            ->withCode($code)
            ->first();
        if (is_null($invitation)) {
            return null;
        }

        $invitation->claimed_on = Carbon::now();
        $invitation->save();
        return $invitation;
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(UserRegistration::class, 'user_registration_id');
    }

    public function senderRegistration(): BelongsTo
    {
        return $this->belongsTo(UserRegistration::class, 'inviter_registration_id');
    }

    public function submit(UserRegistration $registration): UserInvitation
    {
        $invitation = UserInvitation::claim($this->email, $this->token, $this->code);
        $invitation->user_registration_id = $registration->id;
        $invitation->save();
        return $invitation;
    }

    public function getIsClaimedAttribute()
    {
        return (!is_null($this->claimed_on) || !is_null($this->registration));
    }

    /** Scopes */
    public function scopeWithCode(Builder $query, string $code = ''): Builder
    {
        return $query->where('code', $code);
    }

    public function scopeWithToken(Builder $query, string $token = ''): Builder
    {
        return $query->where('token', $token);
    }

    public function scopeWithEmail(Builder $query, string $email = ''): Builder
    {
        return $query->where('email', $email);
    }

    public function scopeWithSender(Builder $query, UserRegistration $sender = null): Builder
    {
        return (is_null($sender))
            ? $query
            : $query->where('inviter_registration_id', $sender->id);
    }

    public function scopeWithType(Builder $query, UserType $type = null): Builder
    {
        return (is_null($type))
            ? $query
            : $query->where('user_type_id', $type->id);
    }
}
