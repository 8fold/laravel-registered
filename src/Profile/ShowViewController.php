<?php

namespace Eightfold\Registered\Profile;

use Eightfold\Registered\Profile\IndexViewController;

use Auth;
use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\RegistersUsers;

use Eightfold\Registered\Registration\UserRegistration;

use Eightfold\Html\Html;
use Eightfold\UIKit\UIKit;

class ShowViewController extends IndexViewController
{
    public function show(Request $request, $username)
    {
        $message = (session('message'))
            ? session('message')
            : null;

        $registration = UserRegistration::withUsername($username)->first();
        $user = $registration->user;
        $isMe = false;
        if (Auth::user() && Auth::user()->isMe($user->username)) {
            $isMe = true;
        }

        $containerClass = $this->getContainerClass($isMe);
        $displayName = $this->getDisplayName($user, $isMe);
        $avatar = $this->getAvatarFigure($registration);
        $siteLinks = $this->getSiteLinks($user);
        $biography = $user->profile->biography;

        $contribute = url($user->registration->profilePath .'/membership');
        $transfer = url($user->registration->profilePath .'/folds/transfer');
        $history = url($user->registration->profilePath .'/folds');

        $main = Html::article([
            parent::getUserNav($isMe),
            Html::div([
                Html::h1($displayName),
                $avatar,
                UIKit::ef_progress(
                    $user->foldAccount->percentToNextLevel,
                    0,
                    100
                )->labels(
                    $user->foldAccount->membershipLevel,
                    $user->foldAccount->totalFolds,
                    $user->foldAccount->nextMembershipLevel
                )->links(
                    UIKit::link(['contribute', $contribute]),
                    UIKit::link(['transfer folds', $transfer]),
                    UIKit::link(['fold history', $history])
                )->attr('class membership-level'),
                $siteLinks,
                UIKit::markdown($biography)
            ]),
        ])->attr('class '. $containerClass);

        return view('main')
            ->with('message', $message)
            ->with('main', $main)
            ->with('page_title', pagetitle([
                $user->displayName,
                'Practitioners',
                '8fold Professionals'
                ])->get()
            );
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

    private function getSiteLinks($user)
    {
        $links = [];
        $links[] = UIKit::link([
            Html::span($user->registration->defaultEmailString)
                ->attr('class ef-sr-only'),
            'mailto:'. $user->registration->defaultEmailString
        ])->glyph('envelope');

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
        return Html::p(array_merge(
            [Html::span('Where to find me online: ')->attr('class ef-sr-only')],
            $links
        ))->attr('class ef-user-site-list');
    }
}
