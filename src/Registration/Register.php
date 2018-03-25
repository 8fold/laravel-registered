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
use Eightfold\LaravelUIKit\Elements\Forms\Form;

class Register extends ControllerBase
{
    use RegistersUsers;

    private $request = null;
    private $inviteToken = false;
    private $inviteRequired = true;
    private $inviteRequestable = false;
    private $siteHasOwner = false;
    private $hasTosLink = false;

    protected $redirectTo = '/register';

    /**
     * Router function to call
     *
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function register(Request $request)
    {
        $token = (is_null($request->token))
            ? ''
            : $request->token;
        $this->request = $request;
        // $this->inviteToken = (isset($request->token));
        $this->inviteRequired = config('registered.invitations.required');
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

        $webView = LaravelUI::web_view(
              'Register | 8fold Professionals'
            , []
            , $this->form($token)
        );
        if ($this->inviteRequired && is_null($request->token)) {
            $alertObject = (object) trans('registered::messages.invitation-required');
            $alert = UIKit::alert($alertObject->title, $alertObject->body);
            $webView = LaravelUI::web_view(
                  'Register | 8fold Professionals'
                , []
                , $alert
                , $this->form($token)
            );
        }

        return view('main')
            ->with('webView', $webView);
    }

    private function form(string $token): Form
    {
        $email = $this->emailInput($token);
        $inviteToken = $this->invitationTokenInput();
        $confirmSelect = $this->tosConfirmationSelect();
        $action = (strlen($token) > 0)
            ? 'post '. url('/register') .'?token='. $token
            : 'post '. url('/register');
        return LaravelUI::form(
                  $action
                , $email
                , LaravelUI::text_input(
                      trans('registered::registration.username')
                    , 'username'
                    , ''
                    , 'johnsmith'
                )->hint(trans('registered::registration.username_hint'))
                , $inviteToken
                , $confirmSelect
                , UIKit::button(trans('registered::registration.register'))
            );
    }

    private function emailInput(string $token)
    {
        $inputValue = '';
        if ($this->inviteRequired && ! is_null($token)) {
            if ($invitation = UserInvitation::withToken($token)->first()) {
                $inputValue = $invitation->email;

            }
        }
        return LaravelUI::text_input(
                  trans('registered::registration.email_address')
                , 'email'
                , $inputValue
                , 'john@8fold.pro'
            );
    }

    private function invitationTokenInput()
    {
        $inviteTokenInput = '';
        if ($this->inviteRequired && UserRegistration::hasSiteOwner()) {
            $inviteTokenInput = LaravelUI::text_input(
                      trans('registered::registration.invitation_code')
                    , 'invite_code'
                );
        }
        return $inviteTokenInput;
    }

    private function tosConfirmationSelect()
    {
        $return = '';
        if ($this->hasTosLink) {
            $return = UIKit::select(
                  'Acknowledge terms of service'
                , 'tos_acceptance'
            )->checkbox()->hideLabel()
            ->options(
                [
                    'true I have read and agree to the',
                    $this->tosLink()
                ]
            );
        }
        return $return;
    }

    private function tosLink()
    {
        $tosLink = '';
        if ($this->hasTosLink) {
            $tosLink = UIKit::link(
                  ' terms of service'
                , url(config('registered.tos_url'))
            );
        }
        return $tosLink;
    }
}
