<?php

namespace Eightfold\Registered\Password;

use Eightfold\Registered\ControllerBase;

use Eightfold\HtmlComponent\Component;
use Eightfold\Html\Html;
use Eightfold\UIKit\UIKit;
use Eightfold\LaravelUIKit\UIKit as LaravelUI;

class ForgotForm extends ControllerBase
{
    public function forgotPasswordForm()
    {
        $headerTitle = 'Request password reset';
        $pageTitle = $headerTitle .' | 8fold Professionals';
        $header = Html::h1(Component::text($headerTitle));
        $content = Html::article(
            LaravelUI::form(
                      'post '. url('/forgot-password')
                    , LaravelUI::text_input('Username', 'username')
                    , LaravelUI::text_input('Email address', 'email')
                    , UIKit::button('Email link')
                )
            );

        $webView = LaravelUI::web_view(
              $pageTitle
            , []
            , $this->message()
            , $header
            , $content
        );

        return view('main')
            ->with('webView', $webView);
    }
}
