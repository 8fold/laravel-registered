<?php

namespace Eightfold\Registered;

use App\Http\Controllers\Controller;

use Auth;
use Eightfold\Registered\Registration\UserRegistration;

use Eightfold\UIKit\Compound\Alert;

use Eightfold\HtmlComponent\Component;
use Eightfold\UIKit\UIKit;
use Eightfold\UIKit\Elements\Simple\UserCard;

/**
 * @deprecated ?? Confirm this can be deprecated. Where is it actually being used?
 */
abstract class ControllerBase extends Controller
{
    static protected function userModelName()
    {
        return config('auth.providers.users.model');
    }

    static protected function hasSiteOwner()
    {
        $count = UserRegistration::withType('owners')
            ->where('confirmed_on', '<>', null)
            ->count();
        return $count > 0;
    }

    static protected function getUserNav(bool $isMe)
    {
        if ($isMe) {
            // user_nav is a global helper function
            // TODO: This is a hacky solution that should be resolved. The hypothesis
            //       is through the natural evolution of the UI Kit, an elegant
            //       solution will emerge that will be easy to implement.
            return user_nav();
        }
        return Component::text('');
    }

    static protected function isMe(string $username): bool
    {
        if (Auth::user() && Auth::user()->isMe($username)) {
            return true;
        }
        return false;
    }

    static protected function message()
    {
        if ($message = session('message')) {
            return $message;
        }
        return Component::text();
    }

    public function __construct()
    {
        $this->middleware(['web']);
    }

    public function back(Alert $alert)
    {
        return back()->with('message', $alert);
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

        return UIKit::user_card(
              Component::text('Picture of '. $userRegistration->displayName)
            , Component::text(url($src))
        );
    }
}
