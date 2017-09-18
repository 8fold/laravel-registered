<?php
namespace Eightfold\Registered\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Eightfold\Registered\Models\UserRegistration;

trait BelongsToUserRegistration
{
    static protected function belongsToUserRegistrationForeignKey(): string
    {
        return 'user_registration_id';
    }

    public function registration(): BelongsTo
    {
        $key = static::belongsToUserRegistrationForeignKey();
        return $this->belongsTo(UserRegistration::class, $key);
    }
}
