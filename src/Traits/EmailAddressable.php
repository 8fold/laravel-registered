<?php
namespace Eightfold\RegistrationManagementLaravel\Traits;

use Validator;

use Eightfold\RegistrationManagementLaravel\Models\UserEmailAddress;
use Eightfold\RegistrationManagementLaravel\Models\UserRegistration;

trait EmailAddressable 
{
    abstract public function emails();

    static public function withEmail(string $email)
    {
        foreach (UserEmailAddress::all() as $e) {
            if ($e->email == $email) {
                return $e->registration;
            }
        }
    }
    
    public function emailWithAddress($email)
    {
        return $this->emails()->where('email', $email)->first();
    }

    /**
     * Allows call of $user->defaultEmailAddress
     *
     * @return String The default email addres of the user
     *
     */
    public function getDefaultEmailStringAttribute()
    {
        return optional($this->defaultEmail)->email;
    }

    /**
     *
     * @return EmailAddress The default email address object
     *
     */
    public function getDefaultEmailAttribute()
    {
        return $this->emails()->where('is_default', true)->first();
    }

    public function addEmail($email, $isDefault = false)
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

    public function setDefaultEmailAttribute($email)
    {
        // Check for default change.
        if ($currentDefault = $this->defaultEmail) {
            if ($this->defaultEmailString !== $email) {
                $currentDefault->is_default = false;
                $currentDefault->save();
            } elseif ($this->defaultEmailString == $email) {
                return;
                
            }
        }
        $address = UserEmailAddress::withAddress($email);
        if (is_null($address)) {
            UserEmailAddress::validator($email)->validate();
            $address = UserEmailAddress::create([
                    'email' => $email,
                    'is_default' => false,
                    'user_registration_id' => $this->id
                ]);
        }
        $address->is_default = true;
        $address->save();
        return $address;
    }   
}