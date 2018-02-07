<?php

namespace Eightfold\Registered\Password;

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

class Create extends ControllerBase
{
    use AuthenticatesUsers;

    /**
     * Set password
     *
     * @param  [type]  $username [description]
     * @param  Request $request  [description]
     * @return [type]            [description]
     */
    public function create($username, Request $request)
    {
        $this->createValidator($request)->validate();

        $registration = UserRegistration::withUsername($username)->first();
        $registration->confirmed_on = Carbon::now();
        $registration->save();

        $user = $registration->user;
        $user->password = $request->password;
        $user->save();

        $this->guard()->login($user);

        return redirect($user->registration->profilePath);
    }

    private function createValidator(Request $request)
    {
        return Validator::make($request->all(), $this->newAndConfirmValidation());
    }

    private function newAndConfirmValidation()
    {
        return [
            'password' => 'required',
            'password_confirm' => 'required|same:password'
        ];
    }
}
