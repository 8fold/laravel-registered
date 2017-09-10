<?php

namespace Eightfold\RegisteredLaravel\Controllers;

use Eightfold\RegisteredLaravel\Controllers\BaseController;

use Auth;
use Validator;
use Illuminate\Http\Request;

use Eightfold\RegisteredLaravel\Models\UserEmailAddress;

class EmailsController extends BaseController
{
    public function addEmailAddress($username, Request $request)
    {
        $this->validatorEmailAddress($request->all())->validate();
        Auth::user()->registration->addEmail($request->email);
        // Redirect, if necessary
        $message = [
            'type' => 'success',
            'title' => 'Successfully added email address',
            'text' => '<p>Your email address was added.</p>'
        ];
        return back()
            ->with('message', $message);
    }

    private function validatorEmailAddress(array $data)
    {
        return Validator::make($data, [
            'email' => UserEmailAddress::validation()
        ]);
    }

    public function makePrimary($username, Request $request)
    {
        $user = Auth::user();
        $registration = $user->registration;

        $toChange = $registration->emailWithAddress($request->address);
        $default = $registration->defaultEmail;
        if ($toChange->email == $default->email) {
            $message = [
                'title' => 'No changes made',
                'text' => '<p>The default address matched the requested address to change to; therefore, no change was made.</p>'
            ];
            return redirect(Auth::user()->registration->editAccountPath)
                ->with('message', $message);
        }

        $toChange->is_default = true;
        $user->email = $toChange->email;
        $toChange->save();
        $user->save();

        $default->is_default = false;
        $default->save();

        $message = [
            'type' => 'success',
            'title' => 'Default address changed',
            'text' => '<p>The default email address for your account was updated.</p>'
        ];
        return back()
            ->with('message', $message);
    }

    public function delete(Request $request)
    {
        $user = Auth::user();
        $addressToDelete = $user->getEmailAddress($request->address);
        $addressToDelete->delete();
        $user->save();

        $message = [
            'type' => 'success',
            'title' => 'Email address successfully deleted'
        ];
        return back()
            ->with('message', $message);
    }
}
