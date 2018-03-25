<?php

namespace Eightfold\Registered\Profile;

use Eightfold\Registered\ControllerBase;

use Auth;
use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\RegistersUsers;

use Eightfold\Registered\Registration\UserRegistration;

use Eightfold\HtmlComponent\Component;
use Eightfold\Html\Html;
use Eightfold\UIKit\UIKit;
use Eightfold\LaravelUIKit\UIKit as LaravelUI;

class Detail extends ControllerBase
{
    public function __construct()
    {
        $this->middleware('web');
    }

    public function show(Request $request, $username)
    {
        // Preparation
        $registration = UserRegistration::withUsername($username)->first();
        $user = $registration->user;
        $userPath = $registration->profilePath;

        // Content
        $headerTitle = $registration->displayName;

        $pageTitle = $headerTitle .' | Practitioners | 8fold Professionals';

        $alert = (session('message'))
            ? session('message')
            : Component::text('');

        $secondaryNav = Component::text('');
        if (Auth::user() && Auth::user()->canManageUsers) {
            $secondaryNav = UIKit::ef_secondary_nav([
                  UIKit::link(['Manage user types',
                        url('/owners/user-types')])
                      ->attr('class ef-button-secondary')
                , UIKit::link(['Manage user', url($userPath .'/manage')])
            ]);
        }

        $isMe = parent::isMe($username);
        $containerClass = $this->getContainerClass($isMe);
        $displayName = $this->getDisplayName($user, $isMe);
        $avatar = $this->getAvatarFigure($registration);
        $memberBar = (Auth::user())
            ? $this->getMemberBar($user)
            : Component::text('');
        $siteLinks = $this->getSiteLinks($user);
        $biography = $user->profile->biography;
        if (is_null($biography)) {
            $biography = '';
        }

        $article = Html::article(
              $alert
            , parent::getUserNav($isMe)
            , Html::div(
                      Html::h1(Component::text($headerTitle))
                    , $avatar
                    , $memberBar
                    , $siteLinks
                    , UIKit::markdown($biography)
                )
            )->attr('class '. $containerClass);

        $webView = LaravelUI::web_view(
              $pageTitle
            , []
            , $article
        );

        return view('main')
            ->with('webView', $webView);

        // return view('main')
        //     ->with('pageTitle', $pageTitle)
        //     ->with('secondaryNav', $secondaryNav)
        //     ->with('content', [$article]);
    }

    private function getContainerClass(bool $isMe = false)
    {
        $containerClass = 'ef-user-profile ef-content';
        if ($isMe) {
            $containerClass = 'ef-my-profile ef-content';
        }
        return $containerClass;
    }

    private function getDisplayName($user, bool $isMe = false)
    {
        $displayName = $user->registration->displayName;
        if ($isMe && $user->isSiteOwner) {
            $displayName .= ' (site owner)';
        }
        return $displayName;
    }

    private function getMemberBar($user)
    {
        $userPath = $user->registration->profilePath;
        $contribute = url($userPath .'/membership');
        $transfer = url($userPath .'/folds/transfer');
        $history = url($userPath .'/folds');

        return UIKit::progress(
                  $user->foldAccount->percentToNextLevel
                , 0
                , 100
            )->labels(
                  $user->foldAccount->membershipLevel
                , $user->foldAccount->totalFolds
                , $user->foldAccount->nextMembershipLevel
            )->links(
                  ['contribute', $contribute]
                , ['transfer folds', $transfer]
                , ['fold history', $history]
            )->attr('class membership-level');
    }

    private function getSiteLinks($user)
    {
        $links = [];
        $links[] = UIKit::link(
              Html::span(Component::text($user->registration->defaultEmailString))
                ->attr('class ef-sr-only')->compile()
            , 'mailto:'. $user->registration->defaultEmailString
        )->glyph('envelope');

        // TODO: Make this into a database table
        $providers = [
            'facebook' => 'facebook-official',
            'twitter' => 'twitter',
            'linkedin' => 'linkedin-square',
            'github' => 'github',
            'medium' => 'medium',
            'leanpub' => 'leanpub',
            'other' => 'globe'
        ];

        foreach ($providers as $type => $icon) {
            $sites = $user->profile->sitesWithType($type);
            foreach ($sites as $site) {
                $links[] = UIKit::link([
                    Html::span($site->address)->attr('class ef-sr-only'),
                    $site->address
                ])->glyph($icon);
            }
        }
        return Html::p(...array_merge(
            [
                Html::span(
                    Component::text('Where to find me online: ')
                )->attr('class ef-sr-only')
            ]
            , $links
        ))->attr('class ef-user-site-list');
    }
}
