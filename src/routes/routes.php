<?php

Route::group([
        'prefix' => 'invitations',
        'middleware' => ['web', 'auth']
    ], function() {
    $invitationController = Eightfold\RegisteredLaravel\Controllers\InvitationController::class;

    Route::get('/', $invitationController.'@index');
    Route::post('/', $invitationController.'@sendInvite');
    Route::post('/{invitation}', $invitationController.'@resendInvite');
});

Route::group([
        'middleware' => ['web']
    ], function() {
    $registerController = Eightfold\RegisteredLaravel\Controllers\RegisterController::class;

    Route::get('register', $registerController.'@showRegistrationForm')
        ->name('register');
    Route::post('register', $registerController.'@register');
    Route::get('registered', $registerController.'@registered');
});

Route::group([
        'middleware' => ['web']
    ], function() {
    $loginController = Eightfold\RegisteredLaravel\Controllers\LoginController::class;

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
});

Route::group([
        'prefix' => 'logout'
    ], function() {
    $loginController = Eightfold\RegisteredLaravel\Controllers\LoginController::class;

    // Logout
    Route::post('/', $loginController.'@logout')
        ->name('logout');
    Route::get('/', function() {
        return redirect('/');
    });
});

$userTypes = [];
if (count(config('registered.user_types')) == 0) {
    $userTypes = Eightfold\RegisteredLaravel\Models\UserType::userTypesForRoutes();

} else {
    $userTypes = config('registered.user_types');

}
foreach ($userTypes as $userPrefix) {
    $prefix = $userPrefix['slug'];

    // User type lists.
    Route::group([
        'middleware' => ['web'],
        'prefix' => $prefix
    ], function() {
        $usersController = Eightfold\RegisteredLaravel\Controllers\UsersController::class;

        Route::get('/', $usersController.'@index');
    });

    // Managing emails.
    Route::group([
        'prefix' => $prefix .'/{username}/account/emails',
        'middleware' => ['web', 'auth', 'registered-only-me']
    ], function() {
        $emailsController = Eightfold\RegisteredLaravel\Controllers\EmailsController::class;

        Route::post('/add', $emailsController.'@addEmailAddress');
        Route::post('/primary', $emailsController.'@makePrimary');
        Route::post('/delete', $emailsController.'@delete');
    });

    // Managing password.
    Route::group([
        'prefix' => $prefix .'/{username}/account',
        'middleware' => ['web', 'auth', 'registered-only-me']
    ], function() {
        $accountController = Eightfold\RegisteredLaravel\Controllers\AccountController::class;

        Route::get('/', $accountController.'@index');
        Route::post('/update-password', $accountController.'@updatePassword');
        // Managing type.
        Route::post('/type', $accountController.'@updateType');
    });

    // Registering password.
    Route::group([
            'prefix' => $prefix .'/{username}',
            'middleware' => ['web']
        ], function() {
        $profileController = Eightfold\RegisteredLaravel\Controllers\ProfileController::class;

        Route::get('/', $profileController.'@index');
        Route::get('/confirm', $profileController.'@confirm')
            ->name('user.confirmaiton');
        Route::get('/set-password', $profileController.'@showEstablishPasswordForm')
            ->name('user.showEstablishPasswordForm');
        Route::post('/set-password', $profileController.'@establishPassword')
            ->name('user.establishPassword');

        // Editing profile.
        Route::get('/edit', $profileController .'@showEditProfile');
        Route::post('/edit/update-names', $profileController .'@updateProfileInformation');
    });
}
