<?php

namespace Eightfold\Registered\userType;

use Eightfold\Registered\ControllerBase;

use View;
use Auth;
use File;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

use Eightfold\Registered\Registration\UserRegistration;
use Eightfold\Registered\UserType\UserType;

use Eightfold\HtmlComponent\Component;
use Eightfold\Html\Html;
use Eightfold\Html\Elements\Grouping\Ul;
use Eightfold\Html\Elements\Grouping\Figure;
use Eightfold\UIKit\UIKit;
use Eightfold\LaravelUIKit\UIKit as LaravelUI;

class Master extends ControllerBase
{
    private $typeSlug = '';

    public function list(Request $request)
    {
        $this->typeSlug = $request->route()->uri;

        $user = (Auth::user())
            ? Auth::user()
            : null;

        if ($this->isInvisibleUserType($request, $user)) {
            abort(404);
        }

        $pageTitle = 'Practitioners | 8fold Professionals';

        $secondaryNav = ( ! is_null($user) && $user->canManageUsers)
            ? $this->getSecondaryNav($user)
            : '';

        $registrationLinks = $this->getRegistrationLinks();

        $article = Html::article($registrationLinks)
            ->attr('class ef-users-list ef-content');

        $webView = LaravelUI::web_view(
              $pageTitle
            , []
            , $article
        );

        return view('main')
            ->with('webView', $webView);
    }

    public function getRegistrationLinks(): Ul
    {
        $registrations = UserRegistration::withType($this->typeSlug)->get();
        if ($this->typeSlug == 'users') {
            $registrations = UserRegistration::all();
        }

        $registrationLinks = [];
        foreach ($registrations as $registration) {
            $registrationLinks[] = Html::li(
                    UIKit::link(
                          $this->getAvatarFigure($registration)
                            ->compile()
                        . Html::span(Component::text('Profile for'))
                            ->compile()
                        . Html::span(Component::text(' '. $registration->displayName))
                            ->compile()

                        , url($registration->profilePath)
                    )
                );
        }
        return Html::ul(...$registrationLinks);
    }

    protected function getSecondaryNav($user)
    {
        return UIKit::secondary_nav(
            UIKit::link('Edit user types', url('/users/types'))
        );
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
