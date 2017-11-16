<?php

namespace Eightfold\Registered\Controllers;

use Eightfold\Registered\Controllers\BaseController;

use Illuminate\Http\Request;

use Eightfold\Html\Html;
use Eightfold\UIKit\UIKit;
use Eightfold\LaravelUIKit\UIKit as LaravelUI;
use Eightfold\LaravelUIKit\Forms\Form;

class PasswordResetViewController extends BaseController
{
    public function index(Request $request)
    {
        $header = 'Reset password';

        $pageTitle = $header .' | 8fold Professionals';

        $form = $this->form($request->token);

        $main = Html::section([
            Html::h1($header),
            $form
        ]);

        return view('main')
            ->with('page_title', $pageTitle)
            ->with('main', $main);
    }

    private function form(string $token): Form
    {
        return LaravelUI::ef_form(
            'post '. url('/reset-password'),
            [
                LaravelUI::ef_text_input(['Username', 'username']),
                LaravelUI::ef_text_input(['Reset code', 'reset_code']),
                LaravelUI::ef_text_input(['New password', 'new_password'])
                    ->password(),
                LaravelUI::ef_text_input(['Confirm password', 'confirm_password'])
                    ->password(),
                UIKit::ef_hidden_input(['reset_token', $token])
            ],
            UIKit::button('Update password')
        );
    }
}
