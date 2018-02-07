<?php

namespace Eightfold\Registered\Authentication;

use Eightfold\Registered\ControllerBase;

use Eightfold\Html\Html;
use Eightfold\UIKit\UIKit;
use Eightfold\LaravelUIKit\UIKit as LaravelUI;

class LoginForm extends ControllerBase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function loginForm()
    {
        $pageTitle = 'Sign in | 8fold Professionals';

        $forgotPasswordLink = Html::p(
            UIKit::link('Forgot password?', '/forgot-password')
        );

        $form = UIKit::form(
              'post '. url('/login')
            , LaravelUI::text_input(
                  trans('registered::registration.username_email')
                , 'username'
            )->hint('samhall or sam@8fold.pro')
            , LaravelUI::text_input(
                  trans('registered::registration.password')
                , 'password'
            )->attr('type password')
            , LaravelUI::csrf_field()
            , UIKit::button(trans('registered::registration.sign_in'))
        );

        $content = Html::article(...[$form, $forgotPasswordLink]);

        $webView = LaravelUI::web_view(
              $pageTitle
            , []
            , $content
        );

        return view('main')
            ->with('webView', $webView);
    }
}
