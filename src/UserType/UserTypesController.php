<?php

namespace Eightfold\Registered\Controllers;

use Eightfold\Registered\Controllers\BaseController;

use Validator;
use Illuminate\Http\Request;

use Eightfold\Registered\Registration\UserRegistration;
use Eightfold\Registered\Models\UserType;

class UserTypesController extends BaseController
{
    // TODO: This is in the wrong place...
    public function store(Request $request)
    {
        Validator::make($request->all(), ['display' => 'required'])->validate();
        UserType::create(['display' => $request->display]);
        return back()
            ->with('message', [
                'type' => 'success',
                'title' => 'User type added'
            ]);
    }

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
