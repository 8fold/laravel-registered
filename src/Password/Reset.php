<?php

namespace Eightfold\Registered\Password;

use Eightfold\Registered\ControllerBase;

use Auth;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Eightfold\Registered\Authentication\UserPasswordReset;
use Eightfold\Registered\Registration\UserRegistration;

class Reset extends ControllerBase
{
    use AuthenticatesUsers;

    public function reset(Request $request)
    {
        $token = $request->reset_token;
        $code = $request->reset_code;
        $username = $request->username;
        $redirect = redirect('/reset-password?token='. $token);

        $reset = UserPasswordReset::withCodeAndToken($code, $token)->first();
        if (is_null($reset)) {
            $alert = UIKit::alert(
                  'Reset code does not match'
                , 'We could not locate a reset request with the provided token and code. Please double check the email and try again.'
            )->error();

            return $redirect->with('message', $alert);
        }

        $user = UserRegistration::withUsername($username)->first()->user;
        if ($reset->registration->user->username !== $user->username) {
            $alert = UIKit::alert(
                  'Username does not match'
                , 'The user associated with the reset request provided does not match the username provided.'
            )->error();

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
            $alert = UIKit::alert(
                  'Password updated'
                , 'Your password was updated successfully.'
            )->success();

            return redirect(Auth::user()->registration->profilePath);
        }

        $alert = UIKit::alert(
              'User information updated'
            , 'Your account information was updated successfully, please sign in.'
        )->success();

        return redirect('/login')->with('message', $alert);
    }
}
