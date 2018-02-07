<?php

namespace Eightfold\Registered;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

use App;
use Illuminate\Support\Facades\Route;

use Eightfold\Registered\Framework\Middlewares\RedirectIfNotMe;

use Eightfold\Registered\Models\UserType;

class Registered extends LaravelServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('registered-only-me', RedirectIfNotMe::class);

        $this->loadMigrationsFrom(__DIR__.'/Framework/DatabaseMigrations');

        $this->publishes([
                __DIR__.'/ConfigBase.php' => config_path('registered.php'),
            ], 'registered-config');

        $useViews = !config('registered.headless.views');
        if ($useViews) {
            $this->loadViewsFrom(__DIR__.'/Registration', 'registration');
            $this->loadViewsFrom(__DIR__.'/Invitation', 'invitation');
            $this->loadViewsFrom(__DIR__.'/Authentication', 'authentication');

            $this->loadTranslationsFrom(
                __DIR__.'/Framework/Localizations', 'registered');

            $this->publishes([
                __DIR__.'/lang' => resource_path('lang/vendor/registered'),
                __DIR__.'/views/layouts/app.blade.php'
                    => resource_path('views/vendor/registered/layouts/app.blade.php'),
                __DIR__.'/views/workflow-registration/part-invitation-alert.blade.php'
                    => resource_path('views/vendor/registered/workflow-registration/part-invitation-alert.blade.php'),
                __DIR__.'/views/account-profile/profile.blade.php'
                    => resource_path('views/vendor/registered/account-profile/profile.blade.php'),
                __DIR__.'/views/account-profile/user-nav.blade.php'
                    => resource_path('views/vendor/registered/account-profile/user-nav.blade.php'),
                __DIR__.'/views/type-homes/'
                    => resource_path('views/vendor/registered/type-homes/')
                ], 'views');
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $discardRoutes = config('registered.headless.routes');
        $discardViews = config('registered.headless.views');
        $useRoutes = (
            ! $discardRoutes &&
            ! $discardViews &&
            ! App::runningUnitTests());
        if ($useRoutes) {
            include __DIR__.'/Routes.php';
        }
    }
}
