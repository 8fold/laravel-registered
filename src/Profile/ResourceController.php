<?php

namespace Eightfold\Registered\Profile;

use Eightfold\Registered\ControllerBase;

use Auth;
use Validator;
// use Carbon\Carbon;
use Illuminate\Http\Request;

// use Illuminate\Foundation\Auth\RegistersUsers;

// use Eightfold\Registered\Registration\UserRegistration;

use Eightfold\UIKit\UIKit;

class ResourceController extends ControllerBase
{
    /**
     * @todo Consider moving to its own resource controller
     *
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function saveAvatar(Request $request)
    {
        if (isset($request->delete) && $request->delete == 'true') {
            return redirect(Auth::user()->registration->profilePath .'/avatar/delete');
        }

        $this->validateAvatar($request->all())->validate();

        $user = Auth::user();

        $userDir = $user->public_key;

        $photoPath = public_path() .'/user_images/'. $userDir .'/';

        $extension = request()->file('profile_picture')->getClientOriginalExtension();

        $imageName = $userDir .'.'. $extension;

        $saved = request()->file('profile_picture')->move($photoPath, $imageName);

        // Delete and save.
        $profile = $user->profile;
        if (!is_null($profile->avatar) && $profile->avatar !== $saved) {
            $profile->deleteAvatar();
        }

        $profile->avatar = $saved->getFilename();
        $profile->save();

        $alert = UIKit::alert([
            'Profile picture updated',
            'Your profile picture has been successfully updated.'
        ])->success();

        return parent::back($alert);
    }

    public function validateAvatar(Array $data)
    {
        return Validator::make($data, [
            'profile_picture' => 'required|image|mimes:jpeg,jpg,png|dimensions:min_width=200,min_height=200,max_width=400,max_height=400,ratio=1/1'
        ]);
    }

    public function deleteAvatar(Request $request)
    {
        if (is_null(Auth::user()->profile->avatar)) {
            $alert = UIKit::alert([
                'No profile picture found',
                'There was no profile picture found for your account.'
            ]);

        } else {
            Auth::user()->profile->deleteAvatar();
            $alert = UIKit::alert([
                'Profile picture successfully deleted',
                'Your profile picture has been removed from the site.'
            ])->success();

        }

        return parent::back($alert);
    }

    /**
     * @todo Consider moving to its own resource controller
     *
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function updateNames(Request $request)
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
        $alert = UIKit::alert([
            'Profile updated successfully',
            'Youʼre profile information was updated successfully.'
        ])->success();

        return parent::back($alert);
    }

    /**
     * @todo Consider moving to its own resource controller
     *
     * @param  [type]  $username [description]
     * @param  Request $request  [description]
     * @return [type]            [description]
     */
    public function updateBiography($username, Request $request)
    {
        $profile = Auth::user()->profile;
        $profile->biography = $request->biography;
        $profile->save();

        $alert = UIKit::alert([
            'Biography updated',
            'Your biography has been successfully updated.'
        ])->success();

        return parent::back($alert);
    }
}
