<?php

namespace Eightfold\Registered\Controllers;



use Eightfold\Registered\Controllers\BaseController;

use Hash;
use Auth;
use Validator;
use Illuminate\Http\Request;

use Eightfold\Registered\Models\UserRegistration;

use Eightfold\Html\Html;
use Eightfold\UIKit\UIkit;
use Eightfold\LaravelUIKit\UIkit as LaravelUI;

class AccountEditViewController extends BaseController
{
    public function index($username, Request $request)
    {
        $header = 'Manage account';

        $user = Auth::user();
        $displayName = $user->displayName;
        $pageTitle = 'Manage Account | '. $displayName .' | 8fold Professionals';
        $userNav = parent::getUserNav($user);
        $passwordSection = $this->getUpdatePasswordSection($user);
        $emailSection = $this->getUpdateEmailAddressesSection($user);

        return view('main')
            ->with('page_title', $pageTitle)
            ->with('main', Html::article([
                $userNav,
                Html::div([
                    Html::h1($header),
                    $passwordSection,
                    $emailSection
                ])
            ])->attr('class ef-my-profile ef-content'));
    }

    private function getUpdatePasswordSection($user)
    {
        $content = '';
        if ($user) {
            $header = Html::h2('Password');
            $form = Html::form([
                LaravelUI::ef_text_input([
                    'Current password',
                    'current_password'
                ]),
                LaravelUI::ef_text_input([
                    'New password',
                    'new_password'
                ])->attr('type password'),
                LaravelUI::ef_text_input([
                    'Confirm new password',
                    'confirm_password'
                ])->attr('type password'),
                LaravelUI::csrf_field(),
                UIKit::ef_button('Update password')
            ])->attr(
                'method post',
                'action '. url($user->registration->editAccountPath .'/update-password')
            );
            $content = Html::section([$header, $form]);
        }
        return $content;
    }

    private function getUpdateEmailAddressesSection($user)
    {
        $content = '';
        if ($user) {
            $header = Html::h2('Email addresses');

            $form = Html::form([
                LaravelUI::ef_text_input([
                    trans('registered::registration.email_address_add'),
                    'email',
                    '',
                    'john@example.com'
                ]),
                LaravelUI::csrf_field(),
                UIKit::ef_button('Save email address')
            ])->attr(
                'method post',
                'action '. url($user->registration->editAccountPath .'/emails')
            );

            $addresses = [];
            $userEmails = $user->registration->emails()
                ->orderBy('is_default', 'DESC')
                ->get();
            foreach ($userEmails as $address) {
                $makeDefault = url(Auth::user()->registration->editAccountPath .
                    '/emails/primary');
                $delete = url(Auth::user()->registration->editAccountPath .
                    '/emails/delete');

                if ($address->is_default) {
                    $addresses[] = [
                        '(default) '. $address->email,
                        ''
                    ];

                } else {
                    $addresses[] = [
                        $address->email,
                        [
                            LaravelUI::ef_action_button([
                                'make default',
                                'post '. $makeDefault,
                                ['address '. $address->email]
                            ]),
                            LaravelUI::ef_action_button([
                                'delete',
                                'post '. $delete,
                                ['address '. $address->email]
                            ])->type('destructuve')
                        ]
                    ];
                }
            }
            $table = UIKit::ef_simple_table(
                $addresses
            )->caption('Registered addresses')->headers('Address', 'Actions');
            $content = Html::section([$header, $form, $table]);
        }
        return $content;
    }
}
