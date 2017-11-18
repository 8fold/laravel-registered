<?php

namespace Eightfold\Registered\Controllers;

use Eightfold\Registered\Controllers\BaseController;

use Hash;
use Auth;
use Validator;
use Illuminate\Http\Request;

use Eightfold\Registered\Registration\UserRegistration;

class AccountController extends BaseController
{
    public function update(Request $request, $username)
    {
        if (!Auth::user()->canChangeUserTypes) {
            return back()
                ->with('message', [
                    'type' => 'error',
                    'title' => 'Not authorized',
                    'body' => '<p>Could not complete your request.</p>'
                ]);
        }

        $registration = UserRegistration::withUsername($username)
            ->first()
            ->updateTypes($request->primary_type, $request->types);

        return back()
            ->with('message', [
                'type' => 'success',
                'title' => 'User types updates successfully.',
                'body' => '<p>The user types for '. $registration->displayName .' were successfully updated. Please look at their record and verify.</p>'
            ]);
    }
}
