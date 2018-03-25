<?php

namespace Eightfold\Registered\Registration;

use Eightfold\Registered\ControllerBase;

use Hash;
use Auth;
use Validator;
use Illuminate\Http\Request;

use Eightfold\Registered\Registration\UserRegistration;

use Eightfold\Html\Html;
use Eightfold\UIKit\UIkit;
use Eightfold\LaravelUIKit\UIkit as LaravelUI;

class UpdateViewController extends ControllerBase
{
    public function update(Request $request, $username)
    {
        $header = 'Manage account';

        $user = Auth::user();

        $displayName = $user->registration->displayName;

        $pageTitle = 'Manage Account | '. $displayName .' | 8fold Professionals';

        $userNav = parent::getUserNav($user->isMe($username));

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
                ])->password(),

                LaravelUI::ef_text_input([
                    'New password',
                    'password'
                ])->password(),

                LaravelUI::ef_text_input([
                    'Confirm new password',
                    'password_confirm'
                ])->password(),

                LaravelUI::csrf_field(),
                UIKit::ef_button('Update password')
            ])->attr(
                'method post',
                'action '. url($user->registration->profilePath .'/update-password')
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

            $form = LaravelUI::ef_form([
                    'post '. url($user->registration->profilePath .'/email-addresses'),
                    [
                        LaravelUI::ef_text_input([
                            trans('registered::registration.email_address_add'),
                            'email',
                            '',
                            'john@example.com'
                        ])
                    ],
                    UIKit::ef_button('Save email address')
                ]);

            $addresses = [];
            $userEmails = $user->registration->emails()
                ->orderBy('is_default', 'DESC')
                ->get();
            foreach ($userEmails as $address) {
                $base = url(Auth::user()->registration->profilePath);
                $makeDefault = $base . '/email-addresses/primary';
                $delete = $base . '/email-addresses/delete';

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
