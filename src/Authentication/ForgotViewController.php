<?php

namespace Eightfold\Registered\Controllers;

use Eightfold\Registered\Controllers\BaseController;

use Eightfold\Html\Html;
use Eightfold\UIKit\UIKit;
use Eightfold\LaravelUIKit\UIKit as LaravelUI;

class PasswordForgotViewController extends BaseController
{
    public function index()
    {
        $pageTitle = 'Request password reset | 8fold Professionals';
        $header = Html::h1('Request password reset');
        $main = LaravelUI::ef_form(
            'post '. url('/forgot-password'),
            [
                LaravelUI::ef_text_input(['Username', 'username']),
                LaravelUI::ef_text_input(['Email address', 'email'])
            ],
            UIKit::ef_button('Email link')
        );
        return view('main')
            ->with('page_title', $pageTitle)
            ->with('main', [$header, $main]);
    }
}
