<?php

namespace Eightfold\Registered\Controllers;

use Eightfold\Registered\Controllers\BaseController;

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

use Eightfold\Registered\Models\UserEmailAddress;
use Eightfold\Registered\Models\UserPasswordReset;
use Eightfold\Registered\Models\UserRegistration;

class LoginController extends BaseController
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

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('registered::workflow-sign-in.login');
    }

    public function login(Request $request)
    {
        $field = filter_var($request->username, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'username';

        if ($field == 'email' && $email = UserEmailAddress::withAddress($request->username)->first()) {
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

    public function showForgotPasswordForm()
    {
        return view('registered::workflow-forgot.password');
    }

    public function processForgotPassword(Request $request)
    {
        $this->validatorEmailAddress($request->all())->validate();

        $email = UserEmailAddress::withAddress($request->email);
        $notUser = !($email->registration->user->username == $request->username);
        if ($notUser) {
            return redirect('/forgot-password')
                ->with('message', [
                    'type' => 'error',
                    'title' => 'Email and username do not match',
                    'body' => '<p>The username entered does not belong to the email address provided; or, vice versa. Please try again.</p>'
                ]);

        }
        $registration = UserRegistration::username($request->username)->first();

        // generate password reset token
        $reset = $registration->passwordReset;
        $token = UserPasswordReset::generateToken(16);
        $code = UserPasswordReset::generateToken(8);
        if (is_null($reset)) {
            $reset = UserPasswordReset::create([
                    'token' => $token,
                    'code' => $code,
                    'user_registration_id' => $registration->id
                ]);

        } else {
            $reset->token = $token;
            $reset->code = $code;
            $reset->save();
        }

        if (!\App::runningUnitTests()) {
            $defaultEmail = $registration->defaultEmail;
            Mail::to($defaultEmail)
                ->send(new UserResetEmailWarn($registration->user));

            if ($defaultEmail !== $request->email) {
                Mail::to($request->email)
                    ->send(new UserResetEmailWarn($registration->user));
            }
        }

        return redirect('/forgot-password')
            ->with('message', [
                'type' => 'success',
                'title' => 'Reset request processed',
                'body' => '<p>We sent an email to the address provided with instructions on resetting your password. Please check your email.</p>'
            ]);
    }

    private function validatorEmailAddress(array $data)
    {
        return Validator::make($data, [
            'email' => 'required|email|exists:user_email_addresses,email',
            'username' => 'required|exists:users,username'
        ]);
    }

    public function showResetPasswordForm()
    {
        return view('registered::workflow-forgot.reset-password');
    }

    public function processResetPasswordForm(Request $request)
    {
        // grab password reset
        $reset = UserPasswordReset::where('code', $request->reset_code)
            ->where('token', $request->reset_token)
            ->first();

        if (is_null($reset)) {
            return redirect('/reset-password?token='. $request->reset_token)
                ->with('message', [
                    'type' => 'error',
                    'title' => 'Reset code does not match',
                    'body' => '<p>We could not locate a reset request with the provided token and code. Please double check the email and try again.</p>'
                ]);
        }


        $user = UserRegistration::username($request->username)->first()->user;
        if ($reset->registration->user->username !== $user->username) {
            return redirect('/reset-password?token='. $request->reset_token)
                ->with('message', [
                    'type' => 'error',
                    'title' => 'Username does not match',
                    'body' => '<p>The user associated with the reset request provided does not match the username provided.</p>'
                ]);
        }

        // we are good and need to actually upate the user record
        $user->password = $request->new_password;
        $user->save();

        // Trying other means to sign in the user do not go well.
        $userLoggedIn = Auth::attempt([
            'username' => $user->username,
            'password' => $request->new_password
        ]);

        if ($userLoggedIn) {
            return redirect(Auth::user()->registration->profilePath);
        }

        return redirect('/login')
            ->with('message', [
                'title' => 'User information updated',
                'body' => '<p>Your account information was updated successfully, please sign in.</p>'
            ]);
    }

    private function validatorResetRequest(array $data)
    {
        return Validator::make($data, [
            'username' => 'required|exists:users,username',
            'reset_code' => 'required',
            'reset_token' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required|same:new_password'
        ]);
    }
}
