<?php

$registeredUserTypes = Eightfold\Registered\UserType\UserType::userTypesForRoutes();

// Invitation -> Registration -> Sign in -> Sign out (forgot and reset)
Route::get('/invitations',
    Eightfold\Registered\Invitation\CreateViewController::class .
        '@index');

Route::post('/invitations',
    Eightfold\Registered\Invitation\ResourceController::class .
        '@send');

// Route::post('/invitations/{invitation}',
//     Eightfold\Registered\Invitation\ResourceController::class .
//         '@resend');

// Route::get('register',
//     Eightfold\Registered\Registration\Register::class .
//         '@register')->name('register');

// Route::post('register',
//     Eightfold\Registered\Registration\ResourceController::class .
//         '@store');

// Route::get('registered',
//     Eightfold\Registered\Registration\ConfirmationMessage::class .
//         '@registered');

// Route::get('login',
//     Eightfold\Registered\Authentication\LoginForm::class .
//         '@loginForm')->name('login');

// Route::post('login',
//     Eightfold\Registered\Authentication\Login::class .
//         '@login');

// Route::post('/logout',
//     Eightfold\Registered\Authentication\Logout::class .
//         '@logout')->name('logout');

// Route::get('/logout',
//         function() { return redirect('/'); })
//     ->middleware('web');

// Route::get('/forgot-password',
//     Eightfold\Registered\Password\ForgotForm::class .
//         '@forgotPasswordForm');

// Route::post('/forgot-password',
//     Eightfold\Registered\Password\SendForgotEmail::class .
//         '@sendForgotPasswordEmail');

// Route::get('/reset-password',
//     Eightfold\Registered\Password\ResetForm::class .
//         '@reset');

// Route::post('/reset-password',
//     Eightfold\Registered\Password\Reset::class .
//         '@reset');

// // Manage user types
// Route::get('/owners/user-types',
//     Eightfold\Registered\UserType\ManageViewController::class .
//         '@manage')->name('add-user-type');

// Route::post('/owners/user-types',
//     Eightfold\Registered\UserType\ResourceController::class .
//         '@store');

// // User areas
// if ( ! \App::runningUnitTests()) {
// foreach ($registeredUserTypes as $userPrefix) {
//     $userTypeSlug = $userPrefix['slug'];

//     // List users of type
//     Route::get($userTypeSlug,
//         Eightfold\Registered\UserType\Master::class .
//             '@list');

//     // Show profile of user
//     Route::get($userTypeSlug .'/{username}',
//         Eightfold\Registered\Profile\Detail::class .
//             '@show');

//     // Manage user
//     Route::get($userTypeSlug .'/{username}/manage',
//         Eightfold\Registered\Authentication\ManageViewController::class .
//             '@manage');

//     Route::patch($userTypeSlug .'/{username}/manage/update-type',
//         Eightfold\Registered\UserType\ResourceController::class .
//             '@update');

//     // Complete registration, once known
//     Route::get($userTypeSlug .'/{username}/confirm-registration',
//         Eightfold\Registered\Registration\ConfirmRedirectController::class .
//             '@redirect');

//     Route::get($userTypeSlug .'/{username}/create-password',
//         Eightfold\Registered\Password\CreateForm::class .
//             '@create')->name($userTypeSlug .'.confirmation');

//     Route::post($userTypeSlug .'/{username}/create-password',
//         Eightfold\Registered\Password\Create::class .
//             '@create')->name($userTypeSlug .'.establishPassword');

//     // Editing passowrd and emails
//     Route::get($userTypeSlug .'/{username}/account',
//         Eightfold\Registered\Registration\UpdateViewController::class .
//             '@update')

//         ->middleware('web', 'auth', 'registered-only-me');

//     Route::get($userTypeSlug .'/{username}/edit',
//         Eightfold\Registered\Profile\UpdateViewController::class .
//             '@edit');

//     Route::post($userTypeSlug .'/{username}/edit',
//         Eightfold\Registered\Profile\ResourceController::class .
//             '@updateNames')

//         ->middleware('web', 'auth', 'registered-only-me');

//     // Editing sites.
//     Route::post($userTypeSlug .'/{username}/sites',
//         Eightfold\Registered\SiteAddress\ResourceController::class .
//             '@create')

//         ->middleware('web', 'auth', 'registered-only-me')
//         ->name('profiled.'. $userTypeSlug .'.add-site');

//     Route::patch($userTypeSlug .'/{username}/sites/{public_key}',
//         Eightfold\Registered\SiteAddress\ResourceController::class .
//             '@update')

//         ->middleware('web', 'auth', 'registered-only-me')
//         ->name('profiled.'. $userTypeSlug .'.update-site');

//     Route::get($userTypeSlug .'/{username}/sites/{public_key}/delete',
//         Eightfold\Registered\SiteAddress\ResourceController::class .
//             '@delete')

//         ->middleware('web', 'auth', 'registered-only-me')
//         ->name('profiled.'. $userTypeSlug .'.delete-site');

//     // Editing password.
//     Route::post($userTypeSlug .'/{username}/update-password',
//         Eightfold\Registered\Authentication\ResourceController::class .
//             '@update')

//         ->middleware('web', 'auth', 'registered-only-me');

//     // Editing emails.
//     Route::post($userTypeSlug .'/{username}/email-addresses',
//         Eightfold\Registered\EmailAddress\ResourceController::class .
//             '@create')

//         ->middleware('web', 'auth', 'registered-only-me');

//     Route::post($userTypeSlug .'/{username}/email-addresses/primary',
//         Eightfold\Registered\EmailAddress\ResourceController::class .
//             '@primary')

//         ->middleware('web', 'auth', 'registered-only-me');

//     Route::post($userTypeSlug .'/{username}/email-addresses/delete',
//         Eightfold\Registered\EmailAddress\ResourceController::class .
//             '@delete')

//         ->middleware('web', 'auth', 'registered-only-me');

//     // Editing avatar.
//     Route::post($userTypeSlug .'/{username}/avatar',
//         Eightfold\Registered\Profile\ResourceController::class .
//             '@saveAvatar')

//         ->middleware('web', 'auth', 'registered-only-me')
//         ->name('profiled.'. $userTypeSlug .'.avatar.add');

//     Route::get($userTypeSlug .'/{username}/avatar/delete',
//         Eightfold\Registered\Profile\ResourceController::class .
//             '@deleteAvatar')

//         ->middleware('web', 'auth', 'registered-only-me')
//         ->name('profiled.'. $userTypeSlug .'.avatar.delete');

//     // Editing biography
//     // TODO: Should probably be `put` method.
//     Route::post($userTypeSlug .'/{username}/biography',
//         Eightfold\Registered\Profile\ResourceController::class .
//             '@updateBiography')

//         ->middleware('web', 'auth', 'registered-only-me');
//     }
// }
