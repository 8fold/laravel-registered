<?php

namespace Eightfold\RegisteredLaravel\Controllers;

use Eightfold\RegisteredLaravel\Controllers\BaseController;

use Auth;
use Validator;
use Illuminate\Http\Request;

use Carbon\Carbon;

use Illuminate\Foundation\Auth\RegistersUsers;

use Eightfold\RegisteredLaravel\Models\UserInvitation;
use Eightfold\RegisteredLaravel\Models\UserEmailAddress;
use Eightfold\RegisteredLaravel\Models\UserRegistration;
use Eightfold\RegisteredLaravel\Models\UserType;

use Eightfold\RegisteredLaravel\Classes\PatreonUser;

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
        $token = $request->token;

        $hasOwner = (UserRegistration::withType('owners')->count() > 0);
        $view = view('registered::workflow-registration.register')
            ->with('invitationRequired', $invitationRequired)
            ->with('invitationRequestable', $invitationRequestable)
            ->with('invitationToken', $token)
            ->with('hasOwner', $hasOwner);
        if ($invitationRequired && is_null($token)) {
            return $view
                ->with('message', [
                    'title' => 'Invitation required',
                    'text' => '<p>Our site is invitation only. If you received an invitation, please try the link in the email again.</p>'
                ]);

        } elseif ($invitationRequired && !is_null($token)) {
            $invitation = UserInvitation::token($request->token)->first();
            $email = $invitation->email;
            return $view->with('email', $email);

        }
        return $view;
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
        if (config('registered.invitations.required')) {
            $invitation = UserInvitation::token($request->invitation_token)
                ->code($request->invite_code)
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
                ? UserType::slug('owners')
                : UserType::slug('users');

        } else {
            $type = UserType::find($invitation->user_type_id);

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
            $token = $request->invitation_token;
            $invitation = UserInvitation::token($token)->first();
            $this->validator($request->all())->validate();
            if (is_null($invitation)) {
                return redirect($this->redirectPath())
                    ->with('message', [
                        'type' => 'warning',
                        'title' => 'Invalid invitation',
                        'text' => '<p>We were unable to find an invitation with that token. Please check the token and try again.</p>'
                    ]);
            }
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
            'invite_code' => 'required|min:6'
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

    /**
     * User is confirming their desire to register for the site.
     *
     * @param  Request $request  [description]
     * @param  String  $username [description]
     * @return Redirect          Redirect user to appropriate location.
     */
    public function confirm(Request $request, string $username)
    {
        $check = $this->didPassSanityCheck($request, $username, false);
        if (is_bool($check) && $check) {
            $registration = UserRegistration::token($request->token)->first();
            $registration->confirmed_on = Carbon::now();
            $registration->save();
            return redirect($registration->setPasswordUrl);
        }
        return $check;
    }

    private function didPassSanityCheck(Request $request, string $username, bool $skipConfirmationCheck = true)
    {
        $registration = UserRegistration::token($request->token)->first();
        $usernamesMatch = ($registration->user->username == $username);
        $unconfirmed = is_null($registration->confirmed_on);

        if ($usernamesMatch && $skipConfirmationCheck) {
            return true;

        } elseif ($usernamesMatch && $unconfirmed && !$skipConfirmationCheck) {
            return true;

        } elseif (!$usernamesMatch) {
            return redirect('/')
                ->with('message', [
                    'type' => 'warning',
                    'title' => 'Incorrect user',
                    'text' => '<p>The user given is not the one associated with the token. Please try again.</p>'
                ]);

        } elseif (!$unconfirmed) {
            return redirect('/login')
                ->with('message', [
                    'title' => 'Already confirmed',
                    'text' => '<p>You have already been confired, please login instead.</p>'
                ]);
        }
        return redirect('/')
            ->with('message', [
                    'type' => 'warning',
                    'title' => 'Unexpected error',
                    'text' => '<p>Yep, I&rsquo;m just as confused as you are. Please try that again.</p>'
                ]);
    }

    /**
     * Allow user to set their password.
     *
     * @param  Request $request  [description]
     * @param  [type]  $username [description]
     * @return [type]            [description]
     */
    public function showEstablishPasswordForm(Request $request, $username)
    {
        $check = $this->didPassSanityCheck($request, $username);
        if (is_bool($check) && $check) {
            return view('registered::workflow-registration.establish-password')
                ->with('message', [
                    'title' => 'Almost done!',
                    'text' => '<p>Now all you need to do is tell us what you want your password to be.</p>'
                ]);
        }
        return $check;
    }

    /**
     * Set the password for the user.
     *
     * @param  Request $request  [description]
     * @param  [type]  $username [description]
     * @return [type]            [description]
     */
    public function establishPassword(Request $request, $username)
    {
        // validate passwords match
        $this->establishPasswordValidator($request->all())->validate();

        // update user with password
        $user = UserRegistration::username($username)->first()->user;
        $user->password = $request->password;
        $user->save();

        // log user in
        $this->guard()->login($user);
        return redirect($user->registration->profilePath);
    }

    protected function establishPasswordValidator(array $data)
    {
        return Validator::make($data, [
            'password' => 'required',
            'password_confirm' => 'required|same:password'
        ]);
    }
}
