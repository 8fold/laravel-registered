<?php

namespace Eightfold\Registered\Controllers;

use Eightfold\Registered\Controllers\BaseController;

use Eightfold\UIKit\UIKit;

class RegisterConfirmationViewController extends BaseController
{
    public function registered()
    {
        $main = UIKit::markdown('# Thank you!'."\n\n".'For additional security we have sent you an email with a link to confirm your registration and set password.'."\n\n".'Again, thank you.');
        return view('main')
            ->with('page_title', 'Registration confirmed | 8fold Professionals')
            ->with('main', $main);
    }

    public function requestInvite(Request $request): RedirectResponse
    {
        UserInvitationRequest::validator($request->email)->validate();
        UserInvitationRequest::create(['email' => $request->email]);
        return back()
            ->with('message', [
                    'type' => 'success',
                    'title' => 'Request submitted',
                    'body' => 'Your invitation request has been submitted successfully.'
                ]);
    }
}
