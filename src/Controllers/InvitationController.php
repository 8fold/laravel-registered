<?php

namespace Eightfold\Registered\Controllers;

use Eightfold\Registered\Controllers\BaseController;

use Auth;
use Validator;
use Illuminate\Http\Request;

use Eightfold\Registered\Models\UserInvitation;
use Eightfold\Registered\Models\UserInvitationRequest;
use Eightfold\Registered\Models\UserType;
use Eightfold\Registered\Models\UserEmailAddress;

class InvitationController extends BaseController
{
    public function index()
    {
        $registration = Auth::user()->registration;
        $inviteCountString = '';
        $canInvite = true;
        if ($registration->user->isSiteOwner()) {
            $inviteCountString = 'You can send an unlimited number of invitations.';

        } else {
            $invitationsMax = 0;
            $invitationsSent = $registration->sentInvitations->count();
            $remainingInvitations = $invitationsMax - $invitationsSent;

            $inviteCountString = 'You have <b>'. $remainingInvitations .'</b> of <b>'. $invitationsMax .' available.</b>';
            $canInvite = ($remainingInvitations > 0) ? true : false;

        }

        $requests = UserInvitationRequest::unsentInvitationRequests();
        $unclaimed = $registration->unclaimedInvitations;
        $claimed = $registration->claimedInvitations;
        $userTypeOptions = UserType::selectOptions();

        return view('registered::workflow-invitation.invitations')
            ->with('inviteCountString', $inviteCountString)
            ->with('requests', $requests)
            ->with('unclaimedInvitations', $unclaimed)
            ->with('claimedInvitations', $claimed)
            ->with('canInvite', $canInvite)
            ->with('userTypeOptions', $userTypeOptions);
    }

    public function sendInvite(Request $request)
    {
        $this->validator($request->all())->validate();
        $email = $request->email;

        $slug = $request->user_type;
        $type = UserType::withSlug($slug)->first();

        $registration = Auth::user()->registration;

        UserInvitation::invite($email, $type, $registration);

        return redirect('invitations')
            ->with('message', [
                    'type' => 'success',
                    'title' => 'Invitation sent!'
                ]);
    }

    public function resendInvite($invitationKey)
    {
        $invitation = UserInvitation::publicKey($invitationKey)->first();
        if (is_null($invitation)) {
            return back()
                ->with('message', [
                        'type' => 'warning',
                        'title' => 'Invitation could not be found',
                        'text' => 'Either I could not locate the invitation or I could not locate the associated sender of the invitation. Please try again.'
                    ]);
        }

        UserInvitation::invite(
            $invitation->email,
            $invitation->type,
            $invitation->senderRegistration);

        return redirect('invitations');
    }

    private function validator(array $data)
    {
        return Validator::make($data, [
            'email' => UserEmailAddress::validation()
        ]);
    }
}
