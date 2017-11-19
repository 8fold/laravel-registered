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

class AuthController extends ControllerBase
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    // contains logout()
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // public $redirectTo = '/users/me';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
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
