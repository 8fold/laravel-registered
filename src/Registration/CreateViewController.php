<?php

namespace Eightfold\Registered\Registration;

use Eightfold\Registered\ControllerBase;

use Auth;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\RegistersUsers;

use Eightfold\Registered\Invitation\UserInvitation;
use Eightfold\Registered\Registration\UserRegistration;

use Eightfold\UIKit\UIKit;
use Eightfold\LaravelUIKit\UIKit as LaravelUI;
use Eightfold\LaravelUIKit\FormControls\InputText;
use Eightfold\LaravelUIKit\Forms\Form;

class CreateViewController extends ControllerBase
{
    use RegistersUsers;

    private $request = null;
    private $inviteToken = false;
    private $inviteRequired = true;
    private $inviteRequestable = false;
    private $siteHasOwner = false;
    private $hasTosLink = false;

    protected $redirectTo = '/register';

    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Router function to call
     *
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function create(Request $request)
    {
        $token = (is_null($request->token))
            ? ''
            : $request->token;
        $this->request = $request;
        // $this->inviteToken = (isset($request->token));
        // $this->inviteRequired = config('registered.invitations.required');
        // $this->inviteRequestable = config('registered.invitations.requestable');
        // $this->siteHasOwner = UserRegistration::hasOwner();
        $this->hasTosLink = (strlen(config('registered.tos_url')) > 0);

        // TODO: This doesn't appear to be working.
        if (Auth::user()) {
            $alertObject = (object) trans('registered::messages.sign-out-first');
            $alert = UIKit::ef_alert([$alertObject->title, $alertObject->body]);
            return redirect(Auth::user()->registration->profilePath)
                ->with('message', $alert);
        }

        $view = view('main')
            ->with('page_title', 'Register | 8fold Professionals')
            ->with('main', $this->form($token));

        if ($this->inviteRequired && is_null($request->token)) {
            $alertObject = (object) trans('registered::messages.invitation-required');
            $alert = UIKit::ef_alert([$alertObject->title, $alertObject->body]);
            return $view->with('message', $alert);

        }

        return $view;
    }

    /**
     * Registration form to use
     *
     * @return [type] [description]
     */
    private function form(string $token): Form
    {
        // $useInviteRequestForm = (
        //     $this->inviteRequired &&
        //     $this->siteHasOwner &&
        //     $this->inviteRequestable &&
        //     ! $this->inviteToken);

        // $form = null;
        // if ($useInviteRequestForm) {
        //     $form = $this->invitationRequestForm();
        // }
        $form = $this->registrationForm($token);

        return $form;
    }

    // private function invitationRequestForm()
    // {
    //     $email = $this->formEmailInput();

    //     return LaravelUI::ef_form([
    //             'post '. url('/register/request-invite'),
    //             [
    //                 $email
    //             ],
    //             UIKit::ef_button(trans('registered::registration.register'))
    //         ]);
    // }

    /**
     * Registration form
     *
     * @return [type] [description]
     */
    private function registrationForm(string $token)
    {
        $email = $this->formEmailInput($token);
        $inviteToken = $this->formInvitationTokenInput();
        $confirmSelect = $this->formTosConfirmationSelect();
        $action = (strlen($token) > 0)
            ? 'post '. url('/register') .'?token='. $token
            : 'post '. url('/register');
        return LaravelUI::ef_form([
                $action,
                [
                    $email,
                    LaravelUI::ef_text_input([
                        trans('registered::registration.username'),
                        'username',
                        '',
                        'johnsmith'
                    ])->hint(trans('registered::registration.username_hint')),
                    $inviteToken,
                    $confirmSelect
                ],
                UIKit::ef_button(trans('registered::registration.register'))
            ]);
    }

    /**
     * Email input field
     *
     * @return [type] [description]
     */
    private function formEmailInput(string $token)
    {
        $inputValue = '';
        if ($this->inviteRequired && ! is_null($token)) {
            if ($invitation = UserInvitation::withToken($token)->first()) {
                $inputValue = $invitation->email;

            }
        }
        return LaravelUI::ef_text_input([
            trans('registered::registration.email_address'),
            'email',
            $inputValue,
            'john@8fold.pro'
        ]);
    }

    /**
     * Registration token input field
     *
     * @return [type] [description]
     */
    private function formInvitationTokenInput()
    {
        $inviteTokenInput = '';
        if ($this->inviteRequired && UserRegistration::hasSiteOwner()) {
            $inviteTokenInput = LaravelUI::ef_text_input([
                trans('registered::registration.invitation_code'),
                'invite_code'
            ]);
        }
        return $inviteTokenInput;
    }

    /**
     * @deprecated ??
     *
     * @return [type] [description]
     */
    private function formInvitationTokenHiddenInput(): InputHidden
    {
        $inviteToken = '';
        if (isset($this->request->token)) {
            $inviteToken = UIKit::ef_hidden_input([
                'invite_code',
                $this->request->token
            ]);
        }
        return $inviteToken;
    }

    /**
     * TOS checkbox
     *
     * @return [type] [description]
     */
    private function formTosConfirmationSelect()
    {
        $return = '';
        if ($this->hasTosLink) {
            $return = UIKit::ef_select([
                'Acknowledge terms of service',
                'tos_acceptance'
            ])->checkbox()->hideLabel()
            ->options(
                [
                    'true I have read and agree to the',
                    $this->formTosLink()
                ]
            );
        }
        return $return;
    }

    /**
     * Link to TOS
     *
     * @return [type] [description]
     */
    private function formTosLink()
    {
        $tosLink = '';
        if ($this->hasTosLink) {
            $tosLink = UIKit::link([
                ' terms of service',
                url(config('registered.tos_url'))
            ]);
        }
        return $tosLink;
    }
}
