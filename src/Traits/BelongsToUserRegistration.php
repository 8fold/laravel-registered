<?php
namespace Eightfold\RegistrationManagementLaravel\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Eightfold\RegistrationManagementLaravel\Models\UserRegistration;

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