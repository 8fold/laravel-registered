<?php

Route::group([
        'prefix' => 'invitations',
        'middleware' => ['web', 'auth']
    ], function() {
    $invitationController = Eightfold\Registered\Controllers\InvitationController::class;

    Route::get('/', $invitationController.'@index');
    Route::post('/', $invitationController.'@sendInvite');
    Route::post('/{invitation}', $invitationController.'@resendInvite');
});

Route::group([
        'middleware' => ['web']
    ], function() {
    $registerController = Eightfold\Registered\Controllers\RegisterController::class;

    Route::get('register', $registerController.'@showRegistrationForm')
        ->name('register');
    Route::post('register', $registerController.'@register');
    Route::get('registered', $registerController.'@registered');
    Route::post('/register/request-invite', $registerController.'@requestInvite');
});

Route::group([
        'middleware' => ['web']
    ], function() {
    $loginController = Eightfold\Registered\Controllers\LoginController::class;

    // Login
    Route::get('login', $loginController.'@showLoginForm')
        ->name('login');
    Route::post('login', $loginController.'@login');

    Route::get('login/patreon', $loginController.'@redirectToProvider');
    Route::get('login/patreon/callback', $loginController.'@handleProviderCallback');

    Route::get('/forgot-password', $loginController.'@showForgotPasswordForm');
    Route::post('/forgot-password', $loginController.'@processForgotPassword');

    Route::get('/reset-password', $loginController.'@showResetPasswordForm');
    Route::post('/reset-password', $loginController.'@processResetPasswordForm');

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
        ], function() use ($usersController) {


            Route::get('/', $usersController.'@index');
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

            Route::get('/', $accountController.'@index');
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
            $profileController = Eightfold\Registered\Controllers\ProfileController::class;

            Route::get('/', $profileController.'@index');
            Route::get('/confirm', $profileController.'@confirm')
                ->name($prefix .'.confirmaiton');
            Route::get('/set-password', $profileController.'@showEstablishPasswordForm')
                ->name($prefix .'.showEstablishPasswordForm');
            Route::post('/set-password', $profileController.'@establishPassword')
                ->name($prefix .'.establishPassword');

            // Editing profile.
            Route::get('/edit', $profileController .'@showEditProfile');
            Route::post('/edit/update-names', $profileController .'@updateProfileInformation');
        });
    }
}
