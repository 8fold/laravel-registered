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

class Update extends ControllerBase
{
    public function update($username, Request $request)
    {
        $this->updateValidator($request)->validate();

        $current = Auth::user()->password;
        if (!Hash::check($request->current_password, $current)) {
            $alert = UIKit::alert([
                'Incorrect current password',
                'The current password supplied does not match the password your account.'
            ])->warning();

            return back()->with('message', $alert);

        } else {
            $alert = UIKit::alert([
                'Password changed',
                'Your password has been updated successfully.'
            ])->success();

        }

        return $this->create($username, $request)->with('message', $alert);
    }
}
