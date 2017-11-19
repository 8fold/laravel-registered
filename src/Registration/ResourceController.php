<?php

namespace Eightfold\Registered\Registration;

use Eightfold\Registered\ControllerBase;

use Auth;
use Validator;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

use Carbon\Carbon;

use Illuminate\Foundation\Auth\RegistersUsers;

use Eightfold\Registered\Invitation\UserInvitation;
use Eightfold\Registered\EmailAddress\UserEmailAddress;
use Eightfold\Registered\UserType\UserType;

use Eightfold\Registered\Registration\UserRegistration;

class ResourceController extends ControllerBase
{
    use RegistersUsers;

    protected $redirectTo = '/register';

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function store(Request $request)
    {
        $this->validateRegistration($request);

        $invitation = null;
        if ($this->invitationRequiredAndHasToken($request)) {
            $invitation = UserInvitation::withToken($request->token)
                ->withCode($request->invite_code)
                ->first();
        } elseif ($this->invitationRequiredAndNoToken($request)) {
            $alert = UIkit::ef_alert([
                        'No invitation found',
                        'Our site is invitation only and we could not locate an invitation with the information you provided. Please try again.'
                    ])->warning();
            return redirect('/register')
                ->with('message', $alert);
        }

        $type = $this->userType($invitation);

        $username = $request->username;
        $email = $request->email;
        if (UserRegistration::registerUser($username, $email, $type, $invitation)) {
            // TODO: Should never have been logged in in the first, not sure why.
            Auth::logout();
            return redirect('/registered');

        }

        $alert = UIkit::ef_alert([
            'An unknown error occurred',
            'Not sure what happened here, please try again.'
        ])->error();

        return redirect('/register')
            ->with('message', $alert);
    }

    private function invitationRequiredAndHasToken(Request $request)
    {
        return (config('registered.invitations.required')
            && ! is_null($request->token));
    }

    private function invitationRequiredAndNoToken(Request $register)
    {
        return (config('registered.invitation.required') && is_null($invitation));
    }

    private function userType(?UserInvitation $invitation): ?UserType
    {
        $type = null;
        if (is_null($invitation)) {
            $type = (UserRegistration::all()->count() == 0)
                ? UserType::withSlug('owners')->first()
                : UserType::withSlug('users')->first();
        }
        return $type;
    }

    private function validateRegistration(Request $request)
    {
        $class = parent::userModelName();
        if (parent::hasSiteOwner()) {
            $this->validator($request->all())->validate();

        } else {
            $this->validatorDefaultOwner($request->all())->validate();

        }
    }

    protected function validator(array $data)
    {
        $inviteRequired = config('registered.invitations.required');
        $tosRequired = (strlen(config('registered.tos_url')) > 0);
        return Validator::make($data, [
            'username' => UserRegistration::usernameValidation(),
            'email' => UserEmailAddress::validation(),
            'invite_code' => ($inviteRequired)
                ? 'required|min:6'
                : '',
            'tos_acceptance' => ($tosRequired)
                ? 'required'
                : ''
        ]);
    }

    protected function validatorDefaultOwner(array $data)
    {
        return Validator::make($data, [
            'username' => UserRegistration::usernameValidation(),
            'email' => UserEmailAddress::validation()
        ]);
    }
}
