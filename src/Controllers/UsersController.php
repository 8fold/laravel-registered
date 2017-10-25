<?php

namespace Eightfold\Registered\Controllers;

use Eightfold\Registered\Controllers\BaseController;

use View;
use Auth;
use File;
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
        if ($type->visible_to == 'all' || (Auth::user() && Auth::user()->canViewType($type))) {
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

            // TODO: Put this on the registration class ??
            // TODO: This depends on classes that aren't part of this package
            //       we either need to merge registered and profiled or figure
            //       something else out.
            $registrationLinks = [];
            foreach ($registrations as $registration) {
                $registrationLinks[] = [
                    'element' => 'li',
                    'content' => [
                        [
                            'element' => 'Eightfold\UIKit\UIKit::link',
                            'href' => url($registration->profilePath),
                            'content' => [
                                $registration->user->profile->avatarFigure,
                                [
                                    // this will be the image container span
                                    'element' => 'span',
                                    'content' => 'Profile for'
                                ],
                                [
                                    // this will be the display text
                                    'element' => 'span',
                                    'content' => ' '. $registration->displayName
                                ]
                            ]
                        ]
                    ]
                ];
            }

            return $view->with('registrations', $registrations)
                ->with('userType', $type)
                ->with('userTypeSelectOptions', $userTypeSelectOptions)
                ->with('registrationLinks', $registrationLinks);
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
