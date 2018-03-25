<?php

namespace Eightfold\Registered\UserType;

use Eightfold\Registered\ControllerBase;

use Auth;
use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

use Eightfold\Registered\Registration\UserRegistration;
use Eightfold\Registered\UserType\UserType;

use Eightfold\Html\Html;

use Eightfold\UIKit\UIKit;
use Eightfold\UIKit\Simple\SimpleTable;

use Eightfold\LaravelUIKit\UIKit as LaravelUI;
use Eightfold\LaravelUIKit\Forms\Form;

class ManageViewController extends ControllerBase
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
    }

    public function manage(Request $request)
    {
        if ( ! Auth::user()->isSiteOwner) {
            abort(404);
        }

        // Content
        $headerTitle = 'Manage user types';

        $pageTitle = $headerTitle .' | Registered | 8fold Professionals';

        $alert = (session('message'))
            ? session('message')
            : null;

        $form = $this->form();

        $table = $this->table(UserType::all());

        // Prepared
        $main = LaravelUI::ef_default_view([
                 $pageTitle // page title is what it is
                , Html::article([
                        Html::h1($headerTitle) // is what it is
                      , $form
                      , $table
                ])
            ])->header($alert);

        return view('main2')->with('main', $main);
    }

    private function form(): Form
    {
        return LaravelUI::ef_form([
            'post '. url('/owners/user-types'),
            [
                LaravelUI::ef_text_input([
                    'User type name',
                    'user_type',
                    '',
                    'Creators'
                ])
            ],
            UIKit::ef_button('Save user type')
        ]);
    }

    private function table(Collection $userTypes): SimpleTable
    {
        $rows = [];
        foreach ($userTypes as $userType) {
            $cells = [];
            $cells[] = $userType->display;
            $cells[] = $userType->slug;
            $cells[] = '';

            $rows[] = $cells;
        }

        return UIKit::ef_simple_table($rows)
            ->headers('Display name', 'Slug', 'Actions');
    }
}
