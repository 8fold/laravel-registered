<?php

namespace Eightfold\RegisteredLaravel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Carbon\Carbon;

use Mail;

use Eightfold\RegisteredLaravel\Mail\UserInvited;

use Eightfold\RegisteredLaravel\Models\UserInvitation;
use Eightfold\RegisteredLaravel\Models\UserRegistration;
use Eightfold\RegisteredLaravel\Models\UserType;

use Eightfold\TraitsLaravel\PublicKeyable;
use Eightfold\RegisteredLaravel\Traits\Typeable;
use Eightfold\RegisteredLaravel\Traits\BelongsToUserRegistration;

class UserInvitation extends Model
{
    use PublicKeyable,
        Typeable,
        BelongsToUserRegistration;

    protected $fillable = [
        'email', 'type', 'token', 'code', 'inviter_registration_id', 'user_type_id'
    ];

    protected $hidden = [
        'token', 'code'
    ];

    static protected function publicKeySalt(): string
    {
        return 'Us3rInv!t@t!0n';
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

        $invitation = static::email($email)
            ->type($type)
            ->sender($sender)
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

        // TODO: Workflow this.
        if (!\App::runningUnitTests()) {
            Mail::to($invitation->email)->send(new UserInvited($invitation));
        }
        return $invitation;
    }

    static public function claim($email, $token, $code): UserInvitation
    {
        $invitation = UserInvitation::email($email)
            ->token($token)
            ->code($code)
            ->first();
        if (is_null($invitation)) {
            return null;
        }

        $invitation->claimed_on = Carbon::now();
        $invitation->save();
        return $invitation;
    }

    public function senderRegistration(): BelongsTo
    {
        return $this->belongsTo(UserRegistration::class, 'inviter_registration_id');
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
