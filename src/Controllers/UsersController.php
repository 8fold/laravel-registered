<?php

namespace Eightfold\Registered\Controllers;

use Eightfold\Registered\Controllers\BaseController;

use View;
use Auth;
use Validator;
use Illuminate\Http\Request;

use Eightfold\Registered\Models\UserRegistration;
use Eightfold\Registered\Models\UserType;

class UsersController extends BaseController
{
    public function index(Request $request)
    {
        $type = $request->route()->uri;
        if ($this->canViewType($type)) {
            $view = (View::exists('registered::type-homes.'. $type .'-home'))
                ? view('registered::type-homes.'. $type .'-home')
                : view('registered::type-homes.users-home');

            $registrations = UserRegistration::withType($type)->get();
            if ($type == 'users') {
                $registrations = UserRegistration::all();
            }

            return $view->with('registrations', $registrations)
                ->with('user_type', $type);
        }
        abort(404);
    }

    private function canViewType($type)
    {
        $myType = 'all';
        if (Auth::user() && $t = Auth::user()->registration->type->slug) {
            $myType = $t;
        }
        $visibleTypes = UserType::where('slug', $type)
            ->first()
            ->visible_to;
        $visibleByTypes = explode(',', $visibleTypes);
        return in_array($myType, $visibleByTypes) || in_array('all', $visibleByTypes);
    }
}
