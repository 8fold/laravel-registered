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
        $typeSlug = $request->route()->uri;
        $type = UserType::withSlug($typeSlug)->first();
        if (Auth::user() && Auth::user()->canViewType($type)) {
            $view = view('registered::type-homes.users-home');

            $typeHasView = View::exists('registered::type-homes.'. $typeSlug .'-home');
            if ($typeHasView) {
                $view = view('registered::type-homes.'. $typeSlug .'-home');
            }

            $registrations = UserRegistration::withType($typeSlug)->get();
            if ($typeSlug == 'users') {
                $registrations = UserRegistration::all();
            }

            $userTypeSelectOptions = UserType::all()
                ->pluck('display', 'slug')
                ->toArray();

            return $view->with('registrations', $registrations)
                ->with('userType', $type)
                ->with('userTypeSelectOptions', $userTypeSelectOptions);
        }
        abort(404);
    }

    public function processAddUserType(Request $request)
    {
        Validator::make($request->all(), ['display' => 'required'])->validate();
        UserType::create(['display' => $request->display]);
        return back()
            ->with('message', [
                'type' => 'success',
                'title' => 'User type added'
            ]);
    }
}
