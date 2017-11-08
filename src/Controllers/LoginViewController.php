<?php

namespace Eightfold\Registered\Controllers;

use Eightfold\Registered\Controllers\BaseController;

use Eightfold\Html\Html;
use Eightfold\UIKit\UIKit;
use Eightfold\LaravelUIKit\UIKit as LaravelUI;

class LoginViewController extends BaseController
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function index()
    {
        $pageTitle = 'Sign in | 8fold Professionals';

        $forgotPasswordLink = Html::p(
            UIKit::link(['Forgot password?', '/forgot-password'])
        );

        $form = Html::form([
            LaravelUI::ef_text_input([
                trans('registered::registration.username_email'),
                'username'
            ])->hint('samhall or sam@8fold.pro'),
            LaravelUI::ef_text_input([
                trans('registered::registration.password'),
                'password'
            ])->attr('type password'),
            LaravelUI::csrf_field(),
            UIKit::ef_button(trans('registered::registration.sign_in'))
        ])->attr('action '. url('/login'));

        return view('main')
            ->with('page_title', $pageTitle)
            ->with('main', [$form, $forgotPasswordLink]);
    }
}
