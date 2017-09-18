<?php

namespace Eightfold\Registered\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

use Eightfold\Registered\Tests\Stubs\User;

use Eightfold\Registered\RegisteredServiceProvider;

use Eightfold\Registered\Models\UserInvitation;
use Eightfold\Registered\Models\UserRegistration;

abstract class TestCase extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--path' => realpath(__DIR__.'/../src/migrations'),
        ]);

        $this->artisan('migrate', ['--database' => 'testing']);
    }

    // use CreatesApplication;
    protected function getPackageProviders($app)
    {
        return [
            RegisteredServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $this->app = $app;

        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $config = $app->make('config');
        $config->set([
            'auth.providers.users.model' => User::class
        ]);
    }

    public function inviteUser()
    {
        $invitation = UserInvitation::invite('someone@example.com');
        $this->assertNull($invitation->claimed_on);
        return $invitation;
    }

    public function registerUser()
    {
        $invitation = $this->inviteUser();

        // POST request data
        $username = 'someone';
        $email = 'someone@example.com';

        $registration = UserRegistration::registerUser($username, $email, null, $invitation);

        $slug = $registration->type->slug;
        $this->assertTrue($slug == 'owners', $slug);
        $this->assertTrue($invitation->isClaimed, $invitation);
        return $registration;
    }
}
