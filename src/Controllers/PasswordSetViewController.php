<?php

namespace Eightfold\Registered\Controllers;

use Eightfold\Registered\Controllers\BaseController;

use Illuminate\Http\Request;

use Eightfold\Registered\Models\UserRegistration;

use Eightfold\Html\Html;
use Eightfold\UIKit\UIKit;
use Eightfold\LaravelUIKit\UIKit as LaravelUI;
use Eightfold\LaravelUIKit\Forms\Form;

class ProfileSetPasswordViewController extends BaseController
{
    public function index(Request $request, string $username)
    {
        $registration = UserRegistration::withToken($request->token)->first();
        if ($this->initialSanityCheckFailed($registration, $username)) {
            return $this->redirect($request, $username);
        }

        $header = 'Set password';

        $pageTitle = $header .' | 8fold Professionals';

        $action = url($registration->profilePath .'/set-password');
        $form = $this->form($action);

        $alert = UIKit::ef_alert([
            'Almost done!',
            'Now all you need to do is tell us what you want your password to be.'
        ]);

        $main = [
            Html::h1($header),
            $form
        ];

        return view('main')
            ->with('page_title', $pageTitle)
            ->with('message', $alert)
            ->with('main', $main);
    }

    private function form(string $action): Form
    {
        return LaravelUI::ef_form([
            'post '. $action,
            [
                LaravelUI::ef_text_input([
                    trans('registered::registration.password'),
                    'password'
                ])->password(),
                LaravelUI::ef_text_input([
                    trans('registered::registration.password_confirm'),
                    'password_confirm'
                ])->password()
            ],
            UIKit::ef_button(trans('registered::registration.set_password'))
        ]);
    }

    public function establishPassword(Request $request, $username)
    {
        // validate passwords match
        $this->establishPasswordValidator($request->all())->validate();

        // update user with password
        $user = UserRegistration::withUsername($username)->first()->user;
        $user->password = $request->password;
        $user->save();

        // log user in
        $this->guard()->login($user);
        return redirect($user->registration->profilePath);
    }

    protected function establishPasswordValidator(array $data)
    {
        return Validator::make($data, [
            'password' => 'required',
            'password_confirm' => 'required|same:password'
        ]);
    }

    private function initialSanityCheckFailed(
        UserRegistration $registration,
        string $username,
        bool $skipConfirmationCheck = true): bool
    {
        $registration = UserRegistration::withToken($registration->token)->first();
        $usernamesMatch = ($registration->user->username == $username);
        $unconfirmed = is_null($registration->confirmed_on);

        if ($usernamesMatch && $skipConfirmationCheck) {
            return false;

        } elseif ($usernamesMatch && $unconfirmed && ! $skipConfirmationCheck) {
            return false;

        }
        return true;
    }

    private function redirect(UserRegistration $registration, string $username)
    {
        $usernamesDoNotMatch = ! ($registration->user->username == $username);
        $alreadyConfirmed = ! is_null($registration->confirmed_on);

        $redirect = '/';
        if ($usernamesDoNotMatch) {
            $alert = UIKit::ef_alert([
                    'Incorrect user',
                    'The user given is not the one associated with the token. Please try again.'
                ])->warning();

        } elseif ($alreadyConfirmed) {
            $redirect = '/login';
            $alert = UIKit::ef_alert([
                    'Already confirmed',
                    'You have already been confired, please login instead.'
            ]);

        } else {
            $alert = UIKit::ef_alert([
                'Unexpected error',
                'Yep, I&rsquo;m just as confused as you are. Please try that again.'
            ])->warning();
        }
        return redirect($redirect)
            ->with('message', $alert);
    }
}
