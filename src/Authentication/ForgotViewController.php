<?php

namespace Eightfold\Registered\Authentication;

use Eightfold\Registered\ControllerBase;

use Eightfold\Html\Html;
use Eightfold\UIKit\UIKit;
use Eightfold\LaravelUIKit\UIKit as LaravelUI;

class ForgotViewController extends ControllerBase
{
    public function __invoke()
    {
        $pageTitle = 'Request password reset | 8fold Professionals';
        $header = Html::h1('Request password reset');
        $main = LaravelUI::ef_form([
            'post '. url('/forgot-password'),
            [
                LaravelUI::ef_text_input(['Username', 'username']),
                LaravelUI::ef_text_input(['Email address', 'email'])
            ],
            UIKit::ef_button('Email link')
        ]);
        return view('main')
            ->with('page_title', $pageTitle)
            ->with('main', [$header, $main]);
    }
}
