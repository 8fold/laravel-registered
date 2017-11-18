<?php

namespace Eightfold\Registered\Profile;

use Eightfold\Registered\ControllerBase;

use View;
use Auth;
use File;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

use Eightfold\Registered\Registration\UserRegistration;
use Eightfold\Registered\UserType\UserType;

use Eightfold\Html\Html;
use Eightfold\Html\Elements\Grouping\Ul;
use Eightfold\Html\Elements\Grouping\Figure;
use Eightfold\UIKit\UIKit;
use Eightfold\UIKit\Simple\UserCard;

class IndexViewController extends ControllerBase
{
    private $typeSlug = '';

    public function index(Request $request)
    {
        $this->typeSlug = $request->route()->uri;

        $user = (Auth::user())
            ? Auth::user()
            : null;

        if ($this->isInvisibleUserType($request, $user)) {
            abort(404);
        }

        $secondaryNav = ( ! is_null($user) && $user->canManageUsers)
            ? $this->getSecondaryNav($user)
            : '';

        $registrationLinks = $this->getRegistrationLinks();

        $main = Html::div(
            Html::article($registrationLinks)
        )->attr('class ef-users-list ef-content');

        return view('main')
            ->with('page_title', 'Practitioners | 8fold Professionals')
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

    /**
     * @todo Should pobably move this to the kit.
     *
     * @param  UserRegistration $userRegistration [description]
     * @return [type]                             [description]
     */
    protected function getAvatarFigure(UserRegistration $userRegistration): UserCard
    {
        $avatar = optional($userRegistration->user->profile)->avatar;
        $src = '';
        if ( ! is_null($avatar) && file_exists(public_path($avatar))) {
            $src = url(substr($avatar, 1));

        } else {
            $src = url('img/logo-jewel.svg');

        }

        return UIKit::ef_user_card([
            'Picture of '. $userRegistration->displayName,
            url($src)
        ]);
    }

    protected function getSecondaryNav($user)
    {
        return UIKit::ef_secondary_nav([
            UIKit::link(['Edit user types', url('/users/types')])
        ]);
    }

    private function isInvisibleUserType(Request $request, $user = null): bool
    {
        $type = UserType::withSlug($this->typeSlug)->first();
        if ($type->visible_to == 'all' || ( ! is_null($user) && $user->canViewType($type))) {
            return false;
        }
        return true;
    }
}
