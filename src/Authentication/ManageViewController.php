<?php

namespace Eightfold\Registered\Authentication;

use Eightfold\Registered\ControllerBase;

use Auth;
use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Eightfold\Registered\Registration\UserRegistration;
use Eightfold\Registered\UserType\UserType;

use Eightfold\Html\Html;
use Eightfold\UIKit\UIKit;
use Eightfold\LaravelUIKit\UIKit as LaravelUI;

class ManageViewController extends ControllerBase
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
    }

    public function manage(Request $request, $username)
    {
        if ( ! Auth::user()->canManageUsers) {
            abort(404);
        }

        // Preparation
        $registration = UserRegistration::withUsername($username)->first();
        $user = $registration->user;
        $userPath = $user->registration->profilePath;

        $isMe = false;
        if (Auth::user() && Auth::user()->isMe($user->username)) {
            $isMe = true;
        }

        // Content
        $headerTitle = 'Manage account of '. $this->getDisplayName($user, $isMe);

        $pageTitle = $headerTitle .' | Practitioners | 8fold Professionals';

        $alert = (session('message'))
            ? session('message')
            : null;

        $containerClass = $this->getContainerClass($isMe);
        $form = $this->getForm($user);

        // Prepared
        $main = LaravelUI::ef_default_view([
              $pageTitle // page title is what it is
            , Html::article([
                  parent::getUserNav($isMe) // isMe should be false, can stay
                , Html::div([
                          Html::h1($headerTitle) // is what it is
                        , $form
                    ])
                ])->attr('class '. $containerClass) // Should be able to remain
            ])->header(
                  $alert // Would be similar across other view controllers
        );

        return view('main2')->with('main', $main);
    }

    private function getDisplayName($user, bool $isMe = false)
    {
        $displayName = $user->registration->displayName;
        if ($isMe && $user->isSiteOwner) {
            $displayName .= ' (site owner)';
        }
        return $displayName;
    }

    private function getContainerClass(bool $isMe = false)
    {
        $containerClass = 'ef-user-profile ef-content';
        if ($isMe) {
            $containerClass = 'ef-my-profile ef-content';
        }
        return $containerClass;
    }

    private function getForm($user)
    {
        $myTypes = $user->registration->types->pluck('slug')->toArray();
        $userTypes = UserType::all();
        $userTypesOptions = [];
        foreach ($userTypes as $userType) {
            $userTypesOptions[] = $userType->slug .' '. $userType->display;

        }

        return LaravelUI::ef_form([
              'patch '. url($user->registration->profilePath) .'/manage/update-type'
            , [
                UIKit::ef_select([
                        'Primary user type',
                        'primary_type',
                        $myTypes
                    ])->options(...$userTypesOptions)
                , UIKit::ef_select([
                        'Other user types',
                        'user_types',
                        $myTypes
                    ])->options(...$userTypesOptions)->checkbox()
            ]
            , UIKit::button('Update user types')
        ]);
    }
}
