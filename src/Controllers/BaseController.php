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
}
