<?php

namespace Eightfold\Registered\Controllers;

use Eightfold\Registered\Controllers\BaseController;

use Auth;
use Validator;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

use Carbon\Carbon;

use Illuminate\Foundation\Auth\RegistersUsers;

use Eightfold\Registered\Models\UserInvitation;
use Eightfold\Registered\Models\UserEmailAddress;
use Eightfold\Registered\Models\UserRegistration;

use Eightfold\Registered\Models\UserType;

class RegisterResourceController extends BaseController
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
            $invitation = UserInvitation::withToken($request->invitation_token)
                ->withCode($request->invite_code)
                ->first();
        }

        if ($this->invitationRequiredAndNoToken($request)) {
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
            && !is_null($request->invitation_token));
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
        if ($class::count() > 0) {
            $this->validator($request->all())->validate();

        } else {
            $this->validatorDefaultOwner($request->all())->validate();

        }
    }

    // TODO: Put this in the model
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'username' => UserRegistration::usernameValidation(),
            'email' => UserEmailAddress::validation(),
            'invite_code' => (strlen(config('registered.invitations.required')) > 0)
                ? 'required|min:6'
                : '',
            'tos_acceptance' => (strlen(config('registered.tos_url')) > 0)
                ? 'required'
                : ''
        ]);
    }

    // TODO: Put this in the model
    protected function validatorDefaultOwner(array $data)
    {
        return Validator::make($data, [
            'username' => UserRegistration::usernameValidation(),
            'email' => UserEmailAddress::validation()
        ]);
    }
}
