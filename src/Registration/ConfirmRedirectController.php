<?php

namespace Eightfold\Registered\Registration;

use Eightfold\Registered\ControllerBase;

use Illuminate\Http\Request;

use Eightfold\UIKit\UIKit;

class ConfirmRedirectController extends ControllerBase
{
    public function redirect(Request $request, string $username)
    {
        $registration = UserRegistration::withToken($request->token)->first();

        $usernamesDoNotMatch = ! ($registration->user->username == $username);
        $alreadyConfirmed = ! is_null($registration->confirmed_on);

        $redirect = '/';
        $alert = '';
        if ($usernamesDoNotMatch) {
            $alert = UIKit::ef_alert([
                    'Incorrect user',
                    'The user given is not the one associated with the token. Please try again.'
                ])->warning();

        } elseif ($alreadyConfirmed) {
            $redirect = '/login';
            $alert = UIKit::ef_alert([
                    'Already confirmed',
                    'You have already been confired, please login instead.'
                ]);

        } else {
            $redirect = url(
                    $registration->profilePath .
                    '/create-password?token='.
                    $request->token
                );
            $alert = UIKit::ef_alert([
                'Almost done!',
                'All you need now is a password.'
            ]);

        }
        return redirect($redirect)
            ->with('message', $alert);
    }
}
