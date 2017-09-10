<?php

namespace Eightfold\RegisteredLaravel\Controllers;

use Eightfold\RegisteredLaravel\Controllers\BaseController;

use View;
use Auth;
use Validator;
use Illuminate\Http\Request;

use Eightfold\RegisteredLaravel\Models\UserRegistration;
use Eightfold\RegisteredLaravel\Models\UserType;

class UsersController extends BaseController
{
    public function index(Request $request)
    {
        $type = $request->route()->uri;
        if ($this->canViewType($type)) {
            $view = (View::exists('registered::type-homes.'. $type .'-home'))
                ? view('registered::type-homes.'. $type .'-home')
                : view('registered::type-homes.users-home');

            $registrations = UserRegistration::ofTypes([$type]);
            if ($type == 'users') {
                $registrations = UserRegistration::all();
            }

            // $userType = UserType::where('slug', $type)->first();
            // $registrations = UserRegistration::where('user_type_id', $userType->id);
            // // TODO: Need a different solution. We need a way to set a same as.
            // // Owner same as practitioner.
            // if ($type == 'practitioners') {
            //     $registrations->orWhere('user_type_id', 1);
            // }
            // $registrations = $registrations->get();

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
