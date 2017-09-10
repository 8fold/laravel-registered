<?php

namespace Eightfold\RegistrationManagementLaravel\Controllers;

use Eightfold\RegistrationManagementLaravel\Controllers\BaseController;

use Hash;
use Auth;
use Validator;
use Illuminate\Http\Request;

use Eightfold\RegistrationManagementLaravel\Models\UserRegistration;

class AccountController extends BaseController
{
    public function index($username, Request $request)
    {
        return view('registered::account-profile.edit-account')
            ->with('user', Auth::user());
    }

    public function updatePassword($username, Request $request)
    {
        $this->validatorPassword($request->all())->validate();
        $current = Auth::user()->password;
        if (!Hash::check($request->current_password, $current)) {
            return back()
                ->with('message', [
                    'type' => 'warning',
                    'title' => 'Incorrect current password',
                    'text' => '<p>The current password supplied does not match the password your account.</p>'
                ]);
        }

        Auth::user()->password = $request->new_password;
        Auth::user()->save();
        return back()
            ->with('message', [
                'type' => 'success',
                'title' => 'Password changed',
                'text' => '<p>Your password has been updated successfully.</p>'
            ]);
    }

    private function validatorPassword(array $data)
    {
        return Validator::make($data, [
            'current_password' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required|same:new_password'
        ]);
    }

    public function updateType(Request $request, $username)
    {
        $registration = UserRegistration::withUsername($username);
        if (UserRegistration::convertToType($registration, $request->type)) {
            return back()
                ->with('message', [
                        'type' => 'success',
                        'title' => 'User type successfully changed'
                    ]);
        }
        return back()
            ->with('message', [
                    'type' => 'error',
                    'title' => 'I was not able to change the user type'
                ]);
    }
}
