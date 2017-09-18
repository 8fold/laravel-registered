<?php

namespace Eightfold\Registered;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Route;

use Eightfold\Registered\Middlewares\RedirectIfNotMe;

use Eightfold\Registered\Models\UserType;

class RegisteredServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $useViews = !config('registered.headless.views');
        if ($useViews) {
            $this->loadViewsFrom(__DIR__.'/views', 'registered');
            $this->loadTranslationsFrom(__DIR__.'/lang', 'registered');

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

        $this->loadMigrationsFrom(__DIR__.'/migrations');
        $this->publishes([
                __DIR__.'/config/registered.php' => config_path('registered.php'),
            ], 'registered-config');

        $router = $this->app['router'];
        $router->aliasMiddleware('registered-only-me', RedirectIfNotMe::class);
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
        $useRoutes = (!$discardRoutes && !$discardViews);
        if ($useRoutes) {
            include __DIR__.'/routes/routes.php';
        }
    }
}
