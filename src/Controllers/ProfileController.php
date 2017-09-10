<?php

namespace Eightfold\RegisteredLaravel\Controllers;

use Eightfold\RegisteredLaravel\Controllers\BaseController;

use Auth;
use Validator;
use Illuminate\Http\Request;

use Eightfold\RegisteredLaravel\Models\UserRegistration;

class ProfileController extends BaseController
{
    public function index(Request $request, $username)
    {
        $isProfileArea = false;
        if (Auth::user()) {
            $trimmedProfilePath = trim(Auth::user()->registration->profilePath, '/');
            $allSubPaths = trim(Auth::user()->registration->profilePath, '/') .'/*';
            if(is_active([$trimmedProfilePath, $allSubPaths])) {
                $isProfileArea = true;

            }
        }
        $message = (session('message'))
            ? session('message')
            : null;

        $canEdit = false;
        if (Auth::user() && Auth::user()->username == $username) {
            $canEdit = true;
        }

        $user = UserRegistration::username($username)->first()->user;
        return view('registered::account-profile.profile')
            ->with('message', $message)
            ->with('user', $user)
            ->with('canEdit', $canEdit)
            ->with('isProfileArea', $isProfileArea);
    }

    public function showEditProfile($username)
    {
        return view('registered::account-profile.profile-edit')
            ->with('user', Auth::user());
    }

    public function updateProfileInformation($username, Request $request)
    {
        $didChangeUserName = false;

        $user = Auth::user();
        if ($user->username !== $request->username) {
            $user->username = $request->username;
            $user->save();
            $didChangeUserName = true;
        }

        $registration = $user->registration;
        $registration->first_name = $request->first_name;
        $registration->last_name = $request->last_name;
        $registration->save();

        //TODO: Create a notification center
        // - email
        // - app internal

        // Redirect, if necessary
        $message = [
            'type' => 'success',
            'title' => 'Profile updated successfully',
            'text' => '<p>You&rsquo;re profile information was updated successfully.</p>'
        ];

        return redirect($registration->editProfilePath)
            ->with('message', $message);
    }
}
