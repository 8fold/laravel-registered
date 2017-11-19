<?php

namespace Eightfold\Registered\Invitation;

use Eightfold\Registered\ControllerBase;

use Auth;
use Validator;
use Illuminate\Http\Request;

use Eightfold\Registered\Invitation\UserInvitation;
use Eightfold\Registered\Invitation\UserInvitationRequest;

use Eightfold\Registered\UserType\UserType;

use Eightfold\Registered\EmailAddress\UserEmailAddress;

Use Eightfold\UIKit\UIKit;

class ResourceController extends ControllerBase
{
    public function send(Request $request)
    {
        Validator::make($request->all(), ['email' => UserEmailAddress::validation()])
            ->validate();

        $email = $request->email;

        $slug = $request->user_type;
        $type = UserType::withSlug($slug)->first();

        $registration = Auth::user()->registration;

        UserInvitation::invite($email, $type, $registration);

        $alert = UIKit::alert([
            'Invitation sent!',
            'The invitation has been sent.'
        ])->success();

        return redirect('invitations')->with('message', $alert);
    }

    public function resend($invitationKey)
    {
        $invitation = UserInvitation::withPublicKey($invitationKey)->first();
        if (is_null($invitation)) {
            $alert = UIKit::alert([
                'Invitation could not be found',
                'Either I could not locate the invitation or I could not locate the associated sender of the invitation. Please try again.'
            ])->warning();

            return back()->with('message', $alert);
        }

        UserInvitation::invite(
            $invitation->email,
            $invitation->type,
            $invitation->senderRegistration);

        return redirect('invitations');
    }
}
