<?php

namespace Eightfold\Registered\EmailAddress;

use Eightfold\Registered\ControllerBase;

use Auth;
use Validator;
use Illuminate\Http\Request;

use Eightfold\Registered\EmailAddress\UserEmailAddress;

use Eightfold\UIKit\UIKit;
use Eightfold\UIKit\Compound\Alert;

class ResourceController extends ControllerBase
{
    public function create($username, Request $request)
    {
        Validator::make($request->all(), ['email' => UserEmailAddress::validation()])
            ->validate();

        Auth::user()->registration->addEmail($request->email);

        $alert = UIKit::ef_alert([
            'Successfully added email address',
            'Your email address was added.'
        ])->success();

        return $this->back($alert);
    }

    public function primary($username, Request $request)
    {
        $user = Auth::user();
        $registration = $user->registration;
        $registration->defaultEmail = $request->address;
        $registration->save();

        $alert = UIKit::ef_alert([
            'Default address changed',
            'The default email address for your account was updated.'
        ])->success();

        return $this->back($alert);
    }

    public function delete(Request $request)
    {
        Auth::user()->registration->deleteEmail($request->address);
        Auth::user()->save();

        $alert = UIKit::ef_alert([
            'Email address deleted',
            'The address was successfully deleted.'
        ])->success();

        return $this->back($alert);
    }
}
