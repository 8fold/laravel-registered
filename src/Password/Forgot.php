<?php

namespace Eightfold\Registered\Authentication;

use Eightfold\Registered\ControllerBase;

use Auth;
use Hash;
use Mail;
use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Eightfold\Registered\Authentication\ResetWarnMailable;

use Eightfold\Registered\Authentication\UserPasswordReset;
use Eightfold\Registered\Registration\UserRegistration;
use Eightfold\Registered\EmailAddress\UserEmailAddress;

use Eightfold\Html\Html;
use Eightfold\UIKit\UIKit;
use Eightfold\LaravelUIKit\UIKit as LaravelUI;

class Forgot extends ControllerBase
{
    public function forgot(Request $request)
    {
        $this->validateEmailAddress($request->all())->validate();

        if ($this->isNotExpectedUser($request)) {
            return $this->redirectForNotMe();
        }

        $registration = UserRegistration::withUsername($request->username)->first();
        $passwordReset = $this->passwordResetForRegistration($registration);
        $this->sendEmail($request, $registration);

        $alert = UIKit::alert([
            'Reset request processed',
            'We sent an email to the address provided with instructions on resetting your password. Please check your email.'
        ])->success();

        return redirect('/forgot-password')
            ->with('message', $alert);
    }

    private function isNotExpectedUser(Request $request):bool
    {
        $userAddress = UserEmailAddress::withAddress($request->email)->firstOrFail();
        return ! ($userAddress->registration->user->username == $request->username);
    }

    private function validateEmailAddress(array $data)
    {
        return Validator::make($data, [
            'email' => 'required|email|exists:user_email_addresses,email',
            'username' => 'required|exists:users,username'
        ]);
    }

    private function redirectForNotMe()
    {
        $alert = UIKit::alert([
            'Email and username do not match',
            'The username entered does not belong to the email address provided; or, vice versa. Please try again.'
        ])->error();
        return redirect('/forgot-password')
            ->with('message', $alert);
    }

    private function passwordResetForRegistration(
        UserRegistration $registration): UserPasswordReset
    {
        $passwordResetForRegistration = $registration->passwordReset;
        $token = UserPasswordReset::generateToken(16);
        $code = UserPasswordReset::generateToken(8);

        if (is_null($passwordResetForRegistration)) {
            $passwordResetForRegistration = UserPasswordReset::create([
                    'token' => $token,
                    'code' => $code,
                    'user_registration_id' => $registration->id
                ]);

        } else {
            $passwordResetForRegistration->token = $token;
            $passwordResetForRegistration->code = $code;
            $passwordResetForRegistration->save();
        }
        return $passwordResetForRegistration;
    }
}
