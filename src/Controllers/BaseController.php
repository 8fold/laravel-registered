<?php

namespace Eightfold\RegisteredLaravel\Controllers;

use App\Http\Controllers\Controller;

abstract class BaseController extends Controller
{
    static protected function userModelName()
    {
        return config('auth.providers.users.model');
    }
}
