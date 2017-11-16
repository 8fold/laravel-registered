<?php

namespace Eightfold\Registered\Controllers;

use Eightfold\Registered\Controllers\BaseController;

use Auth;
use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\RegistersUsers;

use Eightfold\Registered\Models\UserRegistration;

class ProfileController extends BaseController
{
    use RegistersUsers;

    // public function index(Request $request, $username)
    // {
    //     $message = (session('message'))
    //         ? session('message')
    //         : null;

    //     $isProfileArea = UserRegistration::isProfileArea();
    //     $isMyProfile = ($isProfileArea)
    //         ? UserRegistration::isMyProfile($username)
    //         : null;

    //     $user = UserRegistration::withUsername($username)->first()->user;
    //     return view('registered::account-profile.profile')
    //         ->with('message', $message)
    //         ->with('user', $user)
    //         ->with('isMyProfile', $isMyProfile)
    //         ->with('isProfileArea', $isProfileArea)
    //         ->with('page_title', pagetitle([
    //             $user->displayName,
    //             'Practitioners',
    //             '8fold Professionals'
    //             ])->get()
    //         );
    // }

    // public function showEditProfile($username)
    // {
    //     return view('registered::account-profile.profile-edit')
    //         ->with('user', Auth::user());
    // }

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
            'body' => '<p>You&rsquo;re profile information was updated successfully.</p>'
        ];

        return redirect($registration->editProfilePath)
            ->with('message', $message);
    }

    /**
     * User is confirming their desire to register for the site.
     *
     * @param  Request $request  [description]
     * @param  String  $username [description]
     * @return Redirect          Redirect user to appropriate location.
     */
    public function confirm(Request $request, string $username)
    {
        $check = $this->didPassSanityCheck($request, $username, false);
        if (is_bool($check) && $check) {
            $registration = UserRegistration::withToken($request->token)->first();
            $registration->confirmed_on = Carbon::now();
            $registration->save();
            return redirect($registration->setPasswordUrl);
        }
        return $check;
    }

    // private function didPassSanityCheck(Request $request, string $username, bool $skipConfirmationCheck = true)
    // {
    //     $registration = UserRegistration::withToken($request->token)->first();
    //     $usernamesMatch = ($registration->user->username == $username);
    //     $unconfirmed = is_null($registration->confirmed_on);

    //     if ($usernamesMatch && $skipConfirmationCheck) {
    //         return true;

    //     } elseif ($usernamesMatch && $unconfirmed && !$skipConfirmationCheck) {
    //         return true;

    //     } elseif (!$usernamesMatch) {
    //         return redirect('/')
    //             ->with('message', [
    //                 'type' => 'warning',
    //                 'title' => 'Incorrect user',
    //                 'body' => '<p>The user given is not the one associated with the token. Please try again.</p>'
    //             ]);

    //     } elseif (!$unconfirmed) {
    //         return redirect('/login')
    //             ->with('message', [
    //                 'title' => 'Already confirmed',
    //                 'body' => '<p>You have already been confired, please login instead.</p>'
    //             ]);
    //     }
    //     return redirect('/')
    //         ->with('message', [
    //                 'type' => 'warning',
    //                 'title' => 'Unexpected error',
    //                 'body' => '<p>Yep, I&rsquo;m just as confused as you are. Please try that again.</p>'
    //             ]);
    // }

    /**
     * Allow user to set their password.
     *
     * @param  Request $request  [description]
     * @param  [type]  $username [description]
     * @return [type]            [description]
     */
    public function showEstablishPasswordForm(Request $request, $username)
    {
        $check = $this->didPassSanityCheck($request, $username);
        if (is_bool($check) && $check) {
            return view('registered::workflow-registration.establish-password')
                ->with('message', [
                    'title' => 'Almost done!',
                    'body' => '<p>Now all you need to do is tell us what you want your password to be.</p>'
                ]);
        }
        return $check;
    }

    /**
     * Set the password for the user.
     *
     * @param  Request $request  [description]
     * @param  [type]  $username [description]
     * @return [type]            [description]
     */
    public function establishPassword(Request $request, $username)
    {
        // validate passwords match
        $this->establishPasswordValidator($request->all())->validate();

        // update user with password
        $user = UserRegistration::withUsername($username)->first()->user;
        $user->password = $request->password;
        $user->save();

        // log user in
        $this->guard()->login($user);
        return redirect($user->registration->profilePath);
    }

    protected function establishPasswordValidator(array $data)
    {
        return Validator::make($data, [
            'password' => 'required',
            'password_confirm' => 'required|same:password'
        ]);
    }
}
