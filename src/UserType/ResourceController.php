<?php

namespace Eightfold\Registered\UserType;

use Eightfold\Registered\ControllerBase;

use Hash;
use Auth;
use Validator;
use Illuminate\Http\Request;

use Eightfold\Registered\Registration\UserRegistration;

use Eightfold\Registered\UserType\UserType;

use Eightfold\UIKit\UIKit;

class ResourceController extends ControllerBase
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        Validator::make($request->all(), ['user_type' => 'required'])->validate();
        $type = new UserType();
        $type->display = $request->user_type;
        $type->save();

        $alert = UIKit::alert([
            'User type created',
            'The user type '. $type->display .' was created successfully.'
        ])->success();

        return back()->with('message', $alert);
    }

    public function update(Request $request, $username)
    {
        if ( ! Auth::user()->canChangeUserTypes) {
            abort(404);
        }

        $registration = UserRegistration::withUsername($username)
            ->first()
            ->updateTypes($request->primary_type, $request->user_types);

        $alert = UIKit::alert([
            'User types updates successfully.',
            'The user types for '. $registration->displayName .' were successfully updated. Please look at their record and verify.'
        ])->success();

        return back()->with('message', $alert);
    }
}
