<?php

namespace Eightfold\Registered\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Builder;

use Eightfold\Conveniences\Laravel\Attributes\TokenAttribute;

use Eightfold\Registered\Traits\BelongsToUserRegistration;

class UserPasswordReset extends Model
{
    use TokenAttribute,
        BelongsToUserRegistration;

    protected $fillable = [
        'token', 'code', 'user_registration_id'
    ];

    public function scopeWithCodeAndToken(
        Builder $query,
        string $code,
        string $token): Builder
    {
        return $query->where('code', $code)
            ->where('token', $token);
    }
}
