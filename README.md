NOTE: I have temporarily stopped maintaining the views in this package as it is still in serious beta-mode; however, I plan to update them as new methods of handling the presentation layer present themselves.

# Registered for Laravel 5.5+ by 8fold

[Laravel](https://laravel.com) comes with its own registration scaffold, which can work for most projects; Registered takes it farther and takes advantage of some of modern registration user experience patterns including:

- Invitations
  - You can make your site invitation only.
  - You can allow visitors to request an invitation.
- Workflow
  - Invite, register, authenticate, and recover workflows.
  - Invite and registration workflows include email verification step.
  - Optionally require user to accept your terms of service at registration and on update. (Handling the terms of service is *not* handled by Registered.)
  - Sign-in with username *or* email.
- Anonymity
  - Username and email are the only system-required pieces of information to establish an account (you can require more).
  - Users can provide more information (first name, last name, etc.)
  - Users can provide multiple email addresses. (Each of which can be used to sign-in.)
- Other
  - Create mutliple user types, each with their own unique route.
  - Human-friendly user profile path: `/{type-slug}/{username}`.
  - A trait to allow you to easily associate your models with the user's registration record.
  - Two traits (`RegisteredUser` and `RegisteredUserCapabilities`) to put in your user model to gain more control over what the user can do within Registered.

## The `users` table

Registered modifies the `users` table from the base Laravel install in the following ways:

1. **Change** the `name` field to nullable; thereby, removing the requirement to give your name from a system perspective.
2. **Change** the `email` and `password` fields to nullable; thereby, allowing registration and sign-in through third party vendors and applications.
3. **Add** the `username` field, which is required for building the user profile route `/{type->slug}/{username}`.

## What happens on `vendor:publish`?

To keep your project space as clean as possible, we publish the minimum number of files needed to bridge the gap between Registered and your app.

### Config

We do publish a file to `config/registered.php`. This file is required for Registered to work properly. It allows you to tell us whether invitations should be required, if users can request an invitation, and whether or not you want users to have to acknowledge reading and agreeing to a terms of service on registration and TOS updates.

### Views and workflows

To avoid flooding your project with our files and folders, Registered will only publish views we highly recommend you override or that we think you would want to have ready access to. Having said that, you can override any of the views use [the standard Laravel methods](https://laravel.com/docs/5.5/packages#views). Further, we have tried to compartmentalize the views into logical areas.

- **layouts:** Contains a wrapper template and instructions on what Registered need from a front-end perspective.
- **workflow-*:** Each one of these folders contains the view related to the user flow in question. For example, `workfow-invitation` has the view for seeing a list of pending and accepted invitations as well as the email invitation that is sent.
- **account-profile:** Contains the views related to managing your account and profile with the application. From the profile page itself to changing your name in the system to updating email addresses.
- **type-homes:** Every user type you create is given its own base route `/{type-slug}`. If you would like, you can create a unique base page for each user type you create. Just put a blade template in this folder (which is published to your resources directory) and follow this naming convention: {type-slug}-home.blade.php.

## Setup

Require package via composer:

```
$ composer require 8fold/registered
```

Add the service provider to your `config/app.php` providers:

```php
Eightfold\RegistrationManagementLaravel\RegisteredServiceProvider::class
```

Add `UserRegistration` class as an alias to your `config/app.php` aliases:

```php
'Registered' => Eightfold\RegistrationManagementLaravel\Models\UserRegistration::class
```

The alias is only necessary if you are using Registered's views. If not, just skip it.

Publish the package assets (or just the config).

```
$ php artisan vendor:publish [--tag=registered-config]
```

Note: The `registered-config` tag will publish *only* the configuration file. This will allow you to make modifications prior to publishing views. If you would like to go "headless", please use this option. Also, be aware that this will publish all service providers with "registered-config" as a tag.

