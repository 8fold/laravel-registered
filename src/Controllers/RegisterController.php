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
use Eightfold\Registered\Models\UserInvitationRequest;
use Eightfold\Registered\Models\UserType;

use Eightfold\Registered\Classes\PatreonUser;

use Patreon\API;
use Patreon\OAuth;

class RegisterController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/register';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Display the proper registration form.
     *
     * @return View Whether invitation is required.
     */
    public function showRegistrationForm(Request $request)
    {
        // A user is already logged in on this computer.
        // TODO: This doesn't appear to be working.
        if (Auth::user()) {
            return redirect(Auth::user()->registration->profilePath)
                ->with('message', [
                    'title' => 'Sign out first',
                    'text' => '<p>You are signed in to the site elsewhere. Please sign out of that account first.</p><p>We also recommend that you maintain only one account.</p>'
                ]);
        }

        $invitationRequired = config('registered.invitations.required');
        $invitationRequestable = config('registered.invitations.requestable');
        $tosLink = (strlen(config('registered.tos_url')) > 0)
            ? '<a href="'. env('APP_DOMAIN', '') . config('registered.tos_url') .'">terms of service</a>'
            : '';
        $token = $request->token;

        $hasOwner = UserRegistration::hasOwner();
        $view = view('registered::workflow-registration.register')
            ->with('invitationRequired', $invitationRequired)
            ->with('invitationRequestable', $invitationRequestable)
            ->with('invitationToken', $token)
            ->with('tosLink', $tosLink)
            ->with('hasOwner', $hasOwner);
        if ($invitationRequired && is_null($token)) {
            return $view
                ->with('message', [
                    'title' => 'Invitation required',
                    'text' => '<p>Our site is invitation only. If you received an invitation, please try the link in the email again.</p>'
                ]);


        } elseif ($invitationRequired && !is_null($token)) {
            if ($invitation = UserInvitation::withToken($request->token)->first()) {
                $email = $invitation->email;
                return $view->with('email', $email);

            }
        }
        return $view;
    }

    public function requestInvite(Request $request): RedirectResponse
    {
        UserInvitationRequest::validator($request->email)->validate();
        UserInvitationRequest::create(['email' => $request->email]);
        return back()
            ->with('message', [
                    'type' => 'success',
                    'title' => 'Request submitted',
                    'text' => 'Your invitation request has been submitted successfully.'
                ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validateRegistration($request);

        $invitation = null;
        if (config('registered.invitations.required') && !is_null($request->invitation_token)) {
            $invitation = UserInvitation::withToken($request->invitation_token)
                ->withCode($request->invite_code)
                ->first();
        }

        if (config('registered.invitation.required') && is_null($invitation)) {
            return redirect('/register')
                ->with('message', [
                        'type' => 'warning',
                        'title' => 'No invitation found',
                        'text' => '<p>Our site is invitation only and we could not locate an invitation with the information you provided. Please try again.</p>'
                    ]);
        }

        $type = null;
        if (is_null($invitation)) {
            $type = (UserRegistration::all()->count() == 0)
                ? UserType::withSlug('owners')->first()
                : UserType::withSlug('users')->first();

        }

        $username = $request->username;
        $email = $request->email;
        if (UserRegistration::registerUser($username, $email, $type, $invitation)) {
            // TODO: Should never have been logged in in the first, not sure why.
            Auth::logout();
            return redirect('/registered');

        }

        return redirect('/register')
            ->with('message', [
                'type' => 'error',
                'title' => 'An unknown error occurred',
                'text' => '<p>Not sure what happened here, please try again.</p>'
            ]);
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

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
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

    /**
     * The default owner will not need to use an invitation code.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validatorDefaultOwner(array $data)
    {
        return Validator::make($data, [
            'username' => UserRegistration::usernameValidation(),
            'email' => UserEmailAddress::validation()
        ]);
    }

    public function registered()
    {
        return view('registered::workflow-registration.registered');
    }
}
