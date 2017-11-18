<?php

use Eightfold\Registered\Registration\CreateViewController as RegisterVC;
use Eightfold\Registered\Registration\ResourceController as RegistrationRC;
use Eightfold\Registered\Registration\ConfirmationMessageViewController as RegistrationThankYouVC;
use Eightfold\Registered\Registration\ConfirmRedirectController as RegisterConfirmVC;
use Eightfold\Registered\Registration\UpdateViewController as RegisterUpdateVC;

use Eightfold\Registered\Authentication\CreateViewController as PasswordCreateVC;
use Eightfold\Registered\Authentication\ResourceController as PasswordRC;

use Eightfold\Registered\Profile\ShowViewController as ProfileShowVC;
use Eightfold\Registered\Profile\UpdateViewController as ProfileUpdateVC;

use Eightfold\Registered\Authentication\AuthController as AuthC;
use Eightfold\Registered\Authentication\AuthViewController as AuthVC;

use Eightfold\Registered\EmailAddress\ResourceController as EmailRC;

use Eightfold\Registered\Profile\ResourceController as ProfileRC;

use Eightfold\Registered\SiteAddress\ResourceController as SiteRC;

use Eightfold\Registered\Controllers\PasswordForgotViewController;
use Eightfold\Registered\Controllers\PasswordResetViewController;



use Eightfold\Registered\Controllers\InvitationController;
use Eightfold\Registered\Controllers\InvitationCreateViewController;

/**
 * GET  /register - registration form
 * POST /register - process registration form
 * GET  /registered - thank you message
 * GET  {user-types}/{username}/confirm-registration - redirect handler. Passed,
 *          redirect to /create-password. Failed, redirect to /register
 * GET  {user-types}/{username}/create-password - create password form
 * POST {user-types}/{username}/create-password - save password. Redirect
 *          to /{user-types}/{username}
 */

Route::group([
        'prefix' => 'invitations',
        'middleware' => ['web', 'auth']
    ], function() {

    Route::get('/', InvitationCreateViewController::class .'@index');

    Route::post('/', InvitationController::class .'@sendInvite');
    Route::post('/{invitation}', InvitationController::class .'@resendInvite');
});

Route::group([
        'middleware' => ['web']
    ], function() {
    Route::get('register', RegisterVC::class .'@create')
        ->name('register');
    Route::post('register', RegistrationRC::class .'@store');

    // RegisterConfirmationViewController
    Route::get('registered', RegistrationThankYouVC::class .'@registered');

    // InvitationRequestResourceController
    // Route::post('/register/request-invite',
    //     RegisterConfirmationViewController::class .'@requestInvite');

    Route::get('/forgot-password', PasswordForgotViewController::class .'@index');
    Route::post('/forgot-password', PasswordController::class .'@forgot');

    Route::get('/reset-password', PasswordResetViewController::class .'@index');
    Route::post('/reset-password', PasswordController::class .'@reset');

    Route::get('login', AuthVC::class .'@index')
        ->name('login');
    Route::post('login', AuthC::class .'@login');

    Route::post('/logout', AuthC::class .'@logout')
        ->name('logout');
    Route::get('/logout', function() {
        return redirect('/');
    });
});

$usersController = Eightfold\Registered\Controllers\UsersController::class;

Route::post('users/types', $usersController.'@processAddUserType')
    ->middleware('web', 'auth')
    ->name('add-user-type');

if (!\App::runningUnitTests()) {
    $userTypes = Eightfold\Registered\UserType\UserType::userTypesForRoutes();

    foreach ($userTypes as $userPrefix) {
        $userTypeSlug = $userPrefix['slug'];

        // User type lists.
        Route::group([
            'middleware' => ['web'],
            'prefix' => $userTypeSlug
        ], function() {

            Route::get('/',
                Eightfold\Registered\Controllers\UsersIndexViewController::class .
                '@index');
        });

        // View profile
        Route::group([
                'prefix' => $userTypeSlug .'/{username}',
                'middleware' => ['web']
            ], function() use ($userTypeSlug) {

            Route::get('/create-password',
                    PasswordCreateViewController::class .'@create')
                ->name($userTypeSlug .'.confirmation');
            Route::post('/create-password', PasswordController::class .'@create')
                ->name($userTypeSlug .'.establishPassword');


            Route::get('/', ProfileShowVC::class .'@show');

            Route::get('/confirm-registration', RegisterConfirmVC::class .'@redirect');

            Route::get('/create-password', PasswordCreateVC::class .'@create')
                ->name($userTypeSlug .'.confirmation');
            Route::post('/create-password', PasswordRC::class .'@create')
                ->name($userTypeSlug .'.establishPassword');

            // Route::get('/set-password', PasswordSetViewController::class .'@index'
            // )->name($userTypeSlug .'.showEstablishPasswordForm');
            // Route::post('/set-password', PasswordController::class .'@update')
            // Route::get('/set-password', PasswordSetViewController::class .'@index'
            // )->name($userTypeSlug .'.showEstablishPasswordForm');
            // Route::post('/set-password', PasswordController::class .'@update')

        });

        // Editing profile
        Route::group([
                'prefix' => $userTypeSlug .'/{username}',
                'middleware' => ['web', 'auth', 'registered-only-me']
            ], function() use ($userTypeSlug) {
                // View edit profile forms
                Route::get('/account', RegisterUpdateVC::class .'@update');

                // Editing profile.
                Route::get('/edit', ProfileUpdateVC::class .'@edit');
                Route::post('/edit', ProfileRC::class .
                    '@updateNames');

                // Editing sites.
                Route::post('/sites', SiteRC::class .'@create')
                    ->name('profiled.'. $userTypeSlug .'.add-site');
                Route::patch('/sites/{public_key}', SiteRC::class .'@update')
                    ->name('profiled.'. $userTypeSlug .'.update-site');
                Route::get('/sites/{public_key}/delete', SiteRC::class .'@delete')
                    ->name('profiled.'. $userTypeSlug .'.delete-site');

                // Editing password.
                Route::post('/update-password', PasswordRC::class .'@update');

                // Editing emails.
                Route::post('/email-addresses', EmailRC::class .'@create');
                Route::post('/email-addresses/primary', EmailRC::class .'@primary');
                Route::post('/email-addresses/delete', EmailRC::class .'@delete');

                // Editing avatar.
                Route::post('/avatar', ProfileRC::class .'@saveAvatar')
                    ->name('profiled.'. $userTypeSlug .'.avatar.add');
                Route::get('/avatar/delete', ProfileRC::class .'@deleteAvatar')
                    ->name('profiled.'. $userTypeSlug .'.avatar.delete');

                // Editing biography
                // TODO: Should probably be `put` method.
                Route::post('/biography', ProfileRC::class .'@updateBiography');
        });
    }
}
