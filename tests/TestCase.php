<?php

namespace Eightfold\Registered\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

use Eightfold\Registered\Tests\Stubs\User;

use Eightfold\Registered\RegisteredServiceProvider;

use Eightfold\Registered\Models\UserType;
use Eightfold\Registered\Models\UserInvitation;
use Eightfold\Registered\Registration\UserRegistration;

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
            'auth.providers.users.model' => User::class,
            'registered.invitation_required' => true
        ]);
    }

    public function inviteUser($email)
    {
        $invitation = UserInvitation::invite($email);
        $this->assertNull($invitation->claimed_on);
        return $invitation;
    }

    public function registerUser($username = 'someone', $email = 'someone@example.com')
    {
        $invitation = $this->inviteUser($email);

        if ($registration = UserRegistration::registerUser($username, $email, null, $invitation)) {
            $this->assertNotNull($registration->primaryType);
            $this->assertTrue(is_a($registration->primaryType, UserType::class));
            $slug = $registration->primaryType->slug;
            $this->assertTrue($slug == 'owners', $slug);
            $this->assertTrue($invitation->isClaimed, $invitation);
            return $registration;

        } else {
            $this->assertTrue(false, 'Could not register user.');

        }
    }

    public function seedUserTypes()
    {
        UserType::create([
            'display' => 'New types',
            'slug' => 'new-types'
        ]);

        UserType::create([
            'display' => 'Newer types',
            'slug' => 'newer-types'
        ]);
    }
}
