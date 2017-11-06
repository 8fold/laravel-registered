<?php

namespace Eightfold\Registered\Controllers;

use Eightfold\Registered\Controllers\BaseController;

use View;
use Auth;
use File;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

use Eightfold\Registered\Models\UserRegistration;
use Eightfold\Registered\Models\UserType;

use Eightfold\Html\Html;
use Eightfold\Html\Elements\Grouping\Ul;
use Eightfold\Html\Elements\Grouping\Figure;
use Eightfold\UIKit\UIKit;

class UsersIndexViewController extends BaseController
{
    private $typeSlug = '';

    public function index(Request $request)
    {
        $this->typeSlug = $request->route()->uri;

        if ($this->isInvisibleUserType($request)) {
            abort(404);
        }

        $registrationLinks = $this->getRegistrationLinks();

        $main = Html::div(
            Html::article($registrationLinks)
        )->attr('class ef-users-list ef-content');

        return view('registered::type-homes.users-home')
            ->with('main', $main);
    }

    public function getRegistrationLinks(): Ul
    {
        $registrations = UserRegistration::withType($this->typeSlug)->get();
        if ($this->typeSlug == 'users') {
            $registrations = UserRegistration::all();
        }

        $registrationLinks = [];
        foreach ($registrations as $registration) {
            $registrationLinks[] = Html::li(UIKit::link([
                    [
                        $this->getAvatarFigure($registration),
                        Html::span('Profile for'),
                        Html::span(' '. $registration->displayName)
                    ],
                    url($registration->profilePath)
                ]));
        }
        return Html::ul($registrationLinks);
    }

    private function getAvatarFigure(UserRegistration $userRegistration): Figure
    {
        $avatar = optional($userRegistration->user->profile)->avatar;
        $exists = (!is_null($avatar) && file_exists(public_path($avatar)));
        $src = ($exists)
            ? 'src '. url(substr($avatar, 1))
            : 'src '. url('img/logo-jewel.svg');
        $alt = ($exists)
            ? 'alt Profile picture of '. $userRegistration->displayName
            : 'alt Placeholder for profile picture of '. $userRegistration->displayName;

        // ef_image(['alt', 'src'])
        // ef_user_card(['alt', 'src'])->attr('class ef-radial-figure')
        return Html::figure(
            Html::img(false)->attr($src, $alt)
        )->attr('class ef-radial-figure');
    }

    private function isInvisibleUserType(Request $request): bool
    {
        $type = UserType::withSlug($this->typeSlug)->first();
        if ($type->visible_to == 'all' || (Auth::user() && Auth::user()->canViewType($type))) {
            return false;
        }
        return true;
    }
}
