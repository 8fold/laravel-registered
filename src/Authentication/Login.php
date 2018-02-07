<?php

namespace Eightfold\Registered\Authentication;

use Eightfold\Registered\ControllerBase;

use Mail;
use Auth;
use Validator;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Eightfold\Registered\Mail\UserForgotUsernameWarn;
use Eightfold\Registered\Mail\UserForgotUsername;
use Eightfold\Registered\Mail\UserResetEmail;
use Eightfold\Registered\Mail\UserResetEmailWarn;

use Socialite;

use Eightfold\Registered\EmailAddress\UserEmailAddress;
use Eightfold\Registered\Authentication\UserPasswordReset;
use Eightfold\Registered\Registration\UserRegistration;

class Login extends ControllerBase
{
    use AuthenticatesUsers;

    public function __construct()
    {
        parent::__construct();
    }

    public function username()
    {
        return 'username';
    }

    public function login(Request $request)
    {
        $field = filter_var($request->username, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'username';

        if ($field == 'email'
            && $email = UserEmailAddress::withAddress($request->username)->first()) {
            $registration = $email->registration;
            $user = $registration->user;
            $username = $user->username;
            $request->merge(['username' => $username]);
        }
        $this->validateLogin($request);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    protected function redirectTo()
    {
        return Auth::user()->registration->profilePath;
    }
}
