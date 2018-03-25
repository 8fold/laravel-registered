<?php

namespace Eightfold\Registered\Invitation;

use Eightfold\Registered\ControllerBase;

use Auth;
use Illuminate\Http\Request;

use Eightfold\Registered\Invitation\UserInvitation;
use Eightfold\Registered\Invitation\UserInvitationRequest;

use Eightfold\Registered\UserType\UserType;
use Eightfold\Registered\EmailAddress\UserEmailAddress;

use Eightfold\Html\Html;
use Eightfold\UIKit\UIKit;
use Eightfold\LaravelUIKit\UIKit as LaravelUI;

class CreateViewController extends ControllerBase
{
    public function index()
    {
        $user = Auth::user();
        $registration = $user->registration;

        if ($user->isSiteOwner()) {
            $canInvite = true;

        } else {
            $invitationsMax = 0;
            $invitationsSent = $registration->sentInvitations->count();
            $remainingInvitations = $invitationsMax - $invitationsSent;

            $canInvite = ($remainingInvitations > 0) ? true : false;
        }

        $header = 'Invitations';

        $pageTitle = $header .' | 8fold Professionals';

        $inviteCount = $this->inviteCount($user);

        $form = Html::p('You have no more invitations available at this time.');
        if ($canInvite) {
            $form = $this->form($user);
        }

        $unclaimedInvitations = '';
        if (optional($registration->unclaimedInvitations)->count() > 0) {
            $unclaimedInvitations = $this->unclaimedInvitations(
                $registration->unclaimedInvitations->toArray()
            );
        }

        $claimedInvitations = '';
        if (optional($registration->claimedInvitations)->count() > 0) {
            $claimedInvitations = $this->claimedInvitations(
                $registration->claimedInvitations->toArray()
            );
        }

        $main = Html::article([
            Html::h1($header),
            $inviteCount,
            $form,
            $unclaimedInvitations,
            $claimedInvitations
        ]);

        return view('main')
            ->with('page_title', $pageTitle)
            ->with('main', $main);
    }

    private function inviteCount($user)
    {

        $registration = $user->registration;
        if ($user->isSiteOwner()) {
            $inviteCountString = 'You can send an unlimited number of invitations.';

        } else {
            $invitationsMax = 0;
            $invitationsSent = $registration->sentInvitations->count();
            $remainingInvitations = $invitationsMax - $invitationsSent;

            $inviteCountString = 'You have <b>'. $remainingInvitations .'</b> of <b>'. $invitationsMax .' available.</b>';

        }

        return Html::p($inviteCountString);
    }

    private function form($user)
    {
        $userTypeSelect = UIKit::ef_hidden_input(['user_type', 'users']);
        if ($user->canChangeUserTypes) {
            $options = [];
            foreach (UserType::all()->pluck('display', 'slug') as $key => $value) {
                $options[] = $key .' '. $value;
            }

            $userTypeSelect = UIKit::ef_select([
                'Select type of user',
                'user_type',
                ['users']
            ])->options(...$options);
        }

        return LaravelUI::ef_form([
                'post '. url('/invitations'),
                [
                    $userTypeSelect,
                    LaravelUI::ef_text_input([
                        trans('registered::members.email_address'),
                        'email',
                        '',
                        'john@8fold.pro.com'
                    ])
                ],
                UIKit::ef_button(trans('registered::members.invite_member'))
            ]);
    }

    private function unclaimedInvitations(array $invitations)
    {
        if (count($invitations) > 0) {
            $header = Html::h2('Pending invitations');

            $content = array_map(function ($invitation) {
                $form = LaravelUI::ef_form([
                    'post /'. $invitation['public_key'],
                    [],
                    UIKit::ef_button('Send again')
                ]);

                return Html::div([
                    Html::p([
                        Html::b('Sent to: '),
                        $invitation['email']
                    ]),
                    Html::p([
                        Html::b('Sent on: '),
                        $invitation['created_at']
                    ]),
                    $form
                ]);
            }, $invitations);
            return Html::section([$header, $content]);
        }
        return '';
    }

    private function claimedInvitations(array $invitations)
    {
        if (count($invitations) > 0) {
            $header = Html::h2('Claimed invitations');

            $content = array_map(function ($invitation) {
                $form = LaravelUI::ef_form([
                    'post /'. $invitation['public_key'],
                    [],
                    UIKit::ef_button('Send again')
                ]);

                return Html::div([
                    Html::p([
                        Html::b('Sent to: '),
                        $invitation['email']
                    ]),
                    Html::p([
                        Html::b('Sent on: '),
                        $invitation['created_at']
                    ]),
                    Html::p([
                        Html::b('Claimed on: '),
                        $invitation['claimed_on']
                    ]),
                ]);
            }, $invitations);
            return Html::section([$header, $content]);
        }
        return '';
    }
}
