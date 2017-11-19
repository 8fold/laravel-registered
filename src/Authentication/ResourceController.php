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

class ResourceController extends ControllerBase
{
    use AuthenticatesUsers;

    public function create($username, Request $request)
    {
        $this->createValidator($request)->validate();

        $registration = UserRegistration::withUsername($username)->first();
        $registration->confirmed_on = Carbon::now();
        $registration->save();

        $user = $registration->user;
        $this->savePasswordForUser($user, $request);

        $this->guard()->login($user);

        return redirect($user->registration->profilePath);
    }

    public function update($username, Request $request)
    {
        $this->updateValidator($request)->validate();

        $current = Auth::user()->password;
        if (!Hash::check($request->current_password, $current)) {
            $alert = UIKit::alert([
                'Incorrect current password',
                'The current password supplied does not match the password your account.'
            ])->warning();

        } else {
            $alert = UIKit::alert([
                'Password changed',
                'Your password has been updated successfully.'
            ])->success();

            $this->savePasswordForUser(Auth::user(), $request);
        }

        return $this->create($username, $request);
        return back()->with('message', $alert);
    }

    private function savePasswordForUser($user, Request $request)
    {
        $user->password = $request->password;
        $user->save();
    }

    private function createValidator(Request $request)
    {
        return Validator::make($request->all(), $this->newAndConfirmValidation());
    }

    private function updateValidator(Request $request)
    {
        return Validator::make($request->all(), array_merge(
                $this->currentPasswordValidation(),
                $this->newAndConfirmValidation()
            ));
    }

    private function newAndConfirmValidation()
    {
        return [
            'password' => 'required',
            'password_confirm' => 'required|same:password'
        ];
    }

    private function currentPasswordValidation()
    {
        return ['current_password' => 'required'];
    }

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

    private function sendEmail(Request $request, UserRegistration $registration)
    {
        if (!\App::runningUnitTests()) {
            $defaultEmail = $registration->defaultEmail;
            Mail::to($defaultEmail)
                ->send(new ResetWarnMailable($registration->user));

            if ($defaultEmail !== $request->email) {
                Mail::to($request->email)
                    ->send(new ResetWarnMailable($registration->user));

            }
        }
    }

    public function reset(Request $request)
    {
        $token = $request->reset_token;
        $code = $request->reset_code;
        $username = $request->username;
        $redirect = redirect('/reset-password?token='. $token);

        $reset = UserPasswordReset::withCodeAndToken($code, $token)->first();
        if (is_null($reset)) {
            $alert = UIKit::alert([
                'Reset code does not match',
                'We could not locate a reset request with the provided token and code. Please double check the email and try again.'
            ])->error();

            return $redirect->with('message', $alert);
        }

        $user = UserRegistration::withUsername($username)->first()->user;
        if ($reset->registration->user->username !== $user->username) {
            $alert = UIKit::alert([
                'Username does not match',
                'The user associated with the reset request provided does not match the username provided.'
            ])->error();

            return $redirect->with('message', $alert);
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
            $alert = UIKit::alert([
                'Password updated',
                'Your password was updated successfully.'
            ])->success();

            return redirect(Auth::user()->registration->profilePath);
        }

        $alert = UIKit::alert([
            'User information updated',
            'Your account information was updated successfully, please sign in.'
        ])->success();

        return redirect('/login')->with('message', $alert);
    }
}
