<?php

namespace Eightfold\Registered\Controllers;

use Eightfold\Registered\Controllers\BaseController;

use Hash;
use Auth;
use Validator;
use Illuminate\Http\Request;

use Eightfold\Registered\Models\UserRegistration;

class AccountController extends BaseController
{
    // public function updatePassword($username, Request $request)
    // {
    //     $this->validatorPassword($request->all())->validate();
    //     $current = Auth::user()->password;
    //     if (!Hash::check($request->current_password, $current)) {
    //         return back()
    //             ->with('message', [
    //                 'type' => 'warning',
    //                 'title' => 'Incorrect current password',
    //                 'body' => '<p>The current password supplied does not match the password your account.</p>'
    //             ]);
    //     }

    //     Auth::user()->password = $request->new_password;
    //     Auth::user()->save();

    //     return back()
    //         ->with('message', [
    //             'type' => 'success',
    //             'title' => 'Password changed',
    //             'body' => '<p>Your password has been updated successfully.</p>'
    //         ]);
    // }

    // private function validatorPassword(array $data)
    // {
    //     return Validator::make($data, [
    //         'current_password' => 'required',
    //         'new_password' => 'required',
    //         'confirm_password' => 'required|same:new_password'
    //     ]);
    // }

    public function updateType(Request $request, $username)
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
