<?php

namespace Eightfold\Registered\Controllers;

use Eightfold\Registered\Controllers\BaseController;

use Auth;
use Validator;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

use Carbon\Carbon;

use Illuminate\Foundation\Auth\RegistersUsers;

use Eightfold\Registered\Models\UserInvitation;
use Eightfold\Registered\Models\UserEmailAddress;
use Eightfold\Registered\Models\UserRegistration;
use Eightfold\Registered\Models\UserInvitationRequest;
use Eightfold\Registered\Models\UserType;

use Eightfold\Registered\Classes\PatreonUser;

use Patreon\API;
use Patreon\OAuth;

use Eightfold\Html\Html;
use Eightfold\UIKit\UIKit;
use Eightfold\LaravelUIKit\UIKit as LaravelUI;
use Eightfold\LaravelUIKit\FormControls\InputText;
use Eightfold\Html\Elements\Forms\Form;

class RegisterCreateViewController extends BaseController
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

    public function create(Request $request)
    {
        $this->request = $request;
        $this->inviteToken = (isset($request->token));
        $this->inviteRequired = config('registered.invitations.required');
        $this->inviteRequestable = config('registered.invitations.requestable');
        $this->siteHasOwner = UserRegistration::hasOwner();
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
            ->with('main', $this->form());

        if ($this->inviteRequired && is_null($request->token)) {
            $alertObject = (object) trans('registered::messages.invitation-required');
            $alert = UIKit::ef_alert([$alertObject->title, $alertObject->body]);
            return $view->with('message', $alert);

        }

        return $view;
    }

    private function form(): Form
    {
        $useInviteRequestForm = (
            $this->inviteRequired &&
            $this->siteHasOwner &&
            $this->inviteRequestable &&
            ! $this->inviteToken);

        $form = null;
        if ($useInviteRequestForm) {
            $form = $this->invitationRequestForm();
        }
        $form = $this->registrationForm();

        return $form;
    }

    private function invitationRequestForm()
    {
        return Html::form([
                $this->formEmailInput(),
                LaravelUI::csrf_field(),
                UIKit::ef_button(trans('registered::registration.register'))
            ])->attr('action '.  url('/register/request-invite'));
    }

    private function registrationForm()
    {
        return Html::form([
                LaravelUI::csrf_field(),
                $this->formEmailInput(),
                LaravelUI::ef_text_input([
                    trans('registered::registration.username'),
                    'username',
                    '',
                    'johnsmith'
                ])->hint(trans('registered::registration.username_hint')),
                $this->formInvitationTokenInput(),
                $this->formTosConfirmationSelect(),
                UIKit::ef_button(trans('registered::registration.register'))
            ])->attr('action '.  url('/register'));
    }

    private function formEmailInput()
    {
        $inputValue = '';
        if ($this->inviteRequired && ! is_null($this->request->token)) {
            if ($invitation = UserInvitation::withToken($request->token)->first()) {
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

    private function formInvitationTokenInput()
    {
        $inviteTokenInput = '';
        if ($this->inviteRequired && $this->siteHasOwner) {
            $inviteTokenInput = LaravelUI::ef_text_input([
                trans('registered::registration.invitation_code'),
                'invite_code'
            ]);
        }
        return $inviteTokenInput;
    }

    private function formInvitationTokeHiddenInput(): InputHidden
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

    private function formTosConfirmationSelect()
    {
        $return = '';
        if ($this->hasTosLink) {
            UIKit::ef_select([
                'Acknowledge terms of service',
                'tos_acceptance'
            ])->hideLabel()
            ->options(
                [
                    'true I have read and agree to the',
                    $this->formTosLink()
                ]
            );
        }
        return $return;
    }

    private function formTosLink()
    {
        $tosLink = '';
        if ($this->hasTosLink) {
            $tosLink = UIKit::link([
                'terms of service',
                url(config('registered.tos_url'))
            ]);
        }
        return $tosLink;
    }
}
