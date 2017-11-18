<?php

namespace Eightfold\Registered\Profile;

use Eightfold\Registered\ControllerBase;

use Auth;
use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\RegistersUsers;

use Eightfold\Registered\Registration\UserRegistration;

use Eightfold\Html\Html;
use Eightfold\UIKit\UIKit;
use Eightfold\LaravelUIKit\UIKit as LaravelUI;

class UpdateViewController extends ControllerBase
{
    public function edit(string $username)
    {
        $user = Auth::user();

        $header = 'Edit Profile';
        // TODO: Move "8fold Professionals" to the config
        $pageTitle = $header .' | 8fold Professionals';
        $userNav = parent::getUserNav($user);
        $editAvatar = $this->avatarSection($user);
        $editNames = $this->namesSection($user);
        $editSites = $this->siteAddressSection($user);
        $editBiography = $this->biographySection($user);

        if ($user->isSiteOwner) {
            $displayName[] = ' (site owner)';
        }

        return view('main')
            ->with('page_title', $pageTitle)
            ->with('styles', [url('/css/8fold.css'), url('/css/simplemde.min.css')])
            ->with('headScripts', [url('/js/simplemde.min.js')])
            ->with('main', Html::article([
                $userNav,
                Html::div([
                    Html::h1($header),
                    $editAvatar,
                    $editNames,
                    $editSites,
                    $editBiography
                ])
            ])->attr('class ef-my-profile ef-content')
        );
    }

    private function avatarSection($user)
    {
        $return = '';
        if ($user->canAddAvatar) {
            $header = Html::h2('Avatar');
            $avatar = '';
            if ( ! is_null($user->profile->avatar)) {
                $avatar = UIKit::ef_user_card([
                    'Profile image',
                    url($user->profile->avatar)
                ]);
            }

            $avatarSubmitForm = $this->avatarSubmitForm($user);

            $return = Html::section([
                $header,
                $avatar,
                $avatarSubmitForm
            ]);
        }
        return $return;
    }

    private function avatarSubmitForm($user)
    {
        $userTypeSlug = $user->registration->primaryType->slug;
        $formAction = route('profiled.'. $userTypeSlug .'.avatar.add', [
            'username' => $user->username
        ]);

        return LaravelUI::ef_form([
                'post '. $formAction,
                [
                    LaravelUI::ef_file_input([
                        'Profile picture',
                        'profile_picture'
                    ])->hint('PNG or JPG format and at least 200 pixels square (recommend 400 pixels square).')
                    ->optional()
                ],
                [
                    UIKit::ef_button(['Delete picture', 'delete', 'true'])
                        ->type('secondary'),
                    UIKit::ef_button('Upload picture')
                ]
            ])->attr('enctype multipart/form-data');
    }

    private function namesSection($user)
    {
        return Html::section([
            Html::h2('Profile names'),
            LaravelUI::ef_form([
                'post '. url(Auth::user()->registration->profilePath .'/edit'),
                [
                    LaravelUI::ef_text_input([
                        trans('registered::members.username'),
                        'username',
                        $user->username
                    ]),
                    LaravelUI::ef_text_input([
                        trans('registered::members.first_name'),
                        'first_name',
                        $user->registration->first_name
                    ])->optional(),
                    LaravelUI::ef_text_input([
                        trans('registered::members.last_name'),
                        'last_name',
                        $user->registration->last_name
                    ])->optional(),
                ],
                UIKit::ef_button('Save names')
            ])
        ]);
    }

    private function siteAddressSection($user)
    {
        $return = '';
        if ($user->canAddSiteAddresses) {
            $header = Html::h2('Where are you online?');

            $siteSelect = $this->siteTypeSelect('site_type');

            $addForm = LaravelUI::ef_form([
                    'post '. url(Auth::user()->registration->profilePath .'/sites'),
                    [
                        $siteSelect,
                        LaravelUI::ef_text_input([
                            'External site address',
                            'site_address',
                            '',
                            'https://example.com'
                        ])
                    ],
                    UIKit::ef_button('Add site')
                ]);

            $currentSites = $this->currentSitesSubSestion($user);

            $return = Html::section(array_merge(
                [$header, $addForm],
                $currentSites
            ));
        }
        return $return;
    }

    private function siteTypeSelect(string $name, array $selected = [])
    {
        return LaravelUI::ef_select([
                'Site type',
                $name,
                $selected
            ])->options(
                'other Other',
                'facebook Facebook',
                'twitter Twitter',
                'linkedin LinkedIn',
                'github GitHub',
                'medium Medium',
                'leanpub Leanpub'
            )->hideLabel();
    }

    private function currentSitesSubSestion($user): array
    {
        $return = [];
        if ($user->profile->sites()->count() > 0) {
            $header = Html::h3('Current external sites');

            $forms = [];
            foreach ($user->profile->sites as $site) {
                $forms[] = Html::form([
                        Html::div(
                            $this->siteTypeSelect('site_type_'. $site->public_key, [$site->type])
                        )->attr('class ef-width-one-third'),

                        Html::div([
                            LaravelUI::ef_text_input([
                                'External site address',
                                'site_address_'. $site->public_key,
                                $site->address,
                                'https://8fold.pro'
                            ])->hideLabel()
                        ])->attr('class ef-width-one-third'),

                        Html::div([
                            UIKit::ef_button('update'),
                            UIKit::link([
                                'delete',
                                './sites/'. $site->public_key .'/delete'
                            ])->attr('class ef-button'),
                        ])->attr('class ef-width-one-third'),

                        LaravelUI::csrf_field(),
                        LaravelUI::method_field('patch')
                    ])->attr(
                        'class ef-grid-3-column-auto',
                        'method post',
                        'action '.
                            $user->registration->profilePath .
                            '/sites/'.
                            $site->public_key);
            }

            $return = array_merge([$header], $forms);
        }
        return $return;
    }

    private function biographySection($user)
    {
        $return = '';
        if ($user->canCreateBiography) {
            $path = url($user->registration->profilePath .'/biography');
            $form = LaravelUI::ef_form([
                    'post '. $path,
                    [
                        UIKit::ef_markdown_textarea([
                            'Edit your biography',
                            'biography',
                            ( ! is_null($user->profile->biography))
                                ? $user->profile->biography
                                : '',
                            'I started when I was&hellip;'
                        ])->hideLabel()
                        ->optional()
                        ->attr('id biography-editor', 'name biography')
                    ],
                    UIKit::ef_button('Update biography')
                ]);
            $return = Html::section([
                    Html::h2('Edit biography')->attr('id biography'),
                    $form
                ]);
        }
        return $return;
    }
}
