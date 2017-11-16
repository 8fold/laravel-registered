<?php

namespace Eightfold\Registered\Controllers;

use App\Http\Controllers\Controller;

/**
 * @deprecated ?? Confirm this can be deprecated. Where is it actually being used?
 */
abstract class BaseController extends Controller
{
    static protected function userModelName()
    {
        return config('auth.providers.users.model');
    }

    static protected function getUserNav($user)
    {
        // user_nav is a global helper function
        // TODO: This is a hacky solution that should be resolved. The hypothesis is
        //       through the natural evolution of the UI Kit, an elegant solution will
        //       emerge that will be easy to implement.
        return user_nav();
    }
}
