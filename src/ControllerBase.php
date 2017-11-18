<?php

namespace Eightfold\Registered;

use App\Http\Controllers\Controller;

use Eightfold\Registered\Registration\UserRegistration;

use Eightfold\UIKit\Compound\Alert;

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

    static protected function getUserNav($user)
    {
        // user_nav is a global helper function
        // TODO: This is a hacky solution that should be resolved. The hypothesis is
        //       through the natural evolution of the UI Kit, an elegant solution will
        //       emerge that will be easy to implement.
        return user_nav();
    }

    public function back(Alert $alert)
    {
        return back()->with('message', $alert);
    }
}
