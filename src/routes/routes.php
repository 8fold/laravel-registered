<?php

use Eightfold\Registered\Controllers\RegisterResourceController;
use Eightfold\Registered\Controllers\RegisterConfirmationViewController;
use Eightfold\Registered\Controllers\RegisterCreateViewController;

use Eightfold\Registered\Controllers\LoginViewController;

use Eightfold\Registered\Controllers\PasswordController;
use Eightfold\Registered\Controllers\PasswordForgotViewController;
use Eightfold\Registered\Controllers\PasswordSetViewController;
use Eightfold\Registered\Controllers\PasswordResetViewController;

use Eightfold\Registered\Controllers\ProfileController;
use Eightfold\Registered\Controllers\ProfileEditViewController;
use Eightfold\Registered\Controllers\ProfileShowViewController;

use Eightfold\Registered\Controllers\AccountEditViewController;

use Eightfold\Registered\Controllers\InvitationCreateViewController;

Route::group([
        'prefix' => 'invitations',
        'middleware' => ['web', 'auth']
    ], function() {
    $invitationController = Eightfold\Registered\Controllers\InvitationController::class;

    Route::get('/', InvitationCreateViewController::class .'@index');

    Route::post('/', $invitationController.'@sendInvite');
    Route::post('/{invitation}', $invitationController.'@resendInvite');
});

Route::group([
        'middleware' => ['web']
    ], function() {
    Route::get('register', RegisterCreateViewController::class .'@create')
        ->name('register');
    Route::post('register', RegisterResourceController::class .'@store');

    // RegisterConfirmationViewController
    Route::get('registered', RegisterConfirmationViewController::class .'@registered');

    // InvitationRequestResourceControlle
    Route::post('/register/request-invite',
        RegisterConfirmationViewController::class .'@requestInvite');
});

Route::group([
        'middleware' => ['web']
    ], function() {
    $loginController = Eightfold\Registered\Controllers\LoginController::class;

    // Login
    Route::get('login', LoginViewController::class .'@index')
        ->name('login');
    Route::post('login', $loginController.'@login');

    Route::get('login/patreon', $loginController.'@redirectToProvider');
    Route::get('login/patreon/callback', $loginController.'@handleProviderCallback');

    Route::get('/forgot-password', PasswordForgotViewController::class .'@index');
    Route::post('/forgot-password', PasswordController::class .'@forgot');

    Route::get('/reset-password', PasswordResetViewController::class .'@index');
    Route::post('/reset-password', PasswordController::class .'@reset');

    // Logout
    Route::post('/logout', $loginController.'@logout')
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
    $userTypes = Eightfold\Registered\Models\UserType::userTypesForRoutes();
    foreach ($userTypes as $userPrefix) {
        $prefix = $userPrefix['slug'];

        // User type lists.
        Route::group([
            'middleware' => ['web'],
            'prefix' => $prefix
        ], function() {

            Route::get('/',
                Eightfold\Registered\Controllers\UsersIndexViewController::class .
                '@index');
        });

        // Managing emails.
        Route::group([
            'prefix' => $prefix .'/{username}/account/emails',
            'middleware' => ['web', 'auth', 'registered-only-me']
        ], function() {
            $emailsController = Eightfold\Registered\Controllers\EmailsController::class;

            Route::post('/add', $emailsController.'@addEmailAddress');
            Route::post('/primary', $emailsController.'@makePrimary');
            Route::post('/delete', $emailsController.'@delete');
        });

        // Managing password.
        Route::group([
            'prefix' => $prefix .'/{username}/account',
            'middleware' => ['web', 'auth', 'registered-only-me']
        ], function() {
            $accountController = Eightfold\Registered\Controllers\AccountController::class;

            Route::get('/', AccountEditViewController::class .'@index');
            Route::post('/update-password', $accountController.'@updatePassword');
        });

        // Managing type.
        Route::group([
            'prefix' => $prefix .'/{username}/account',
            'middleware' => ['web', 'auth']
        ], function() {
            $accountController = Eightfold\Registered\Controllers\AccountController::class;
            Route::post('/type', $accountController.'@updateType');
        });

        // Registering password.
        Route::group([
                'prefix' => $prefix .'/{username}',
                'middleware' => ['web']
            ], function() use ($prefix) {

            Route::get('/', ProfileShowViewController::class .'@show');

            Route::get('/confirm', ProfileController::class .'@confirm')
                ->name($prefix .'.confirmaiton');

            Route::get(
                '/set-password',
                PasswordSetViewController::class .'@index'
            )->name($prefix .'.showEstablishPasswordForm');

            Route::post(
                '/set-password',
                ProfileController::class .'@establishPassword'
            )->name($prefix .'.establishPassword');

            Route::group([
                    'middleware' => ['auth', 'registered-only-me']
                ], function() {
                    // Editing profile.
                    Route::get('/edit', ProfileEditViewController::class .'@edit');
                    Route::post('/edit/update-names', ProfileController::class .'@updateProfileInformation');
            });
        });
    }
}
