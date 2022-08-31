<?php

namespace Dcblogdev\Xero;

use Dcblogdev\Xero\Console\Commands\XeroKeepAliveCommand;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Dcblogdev\Xero\XeroAuthenticated;

class XeroServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->registerCommands();
        $this->registerMiddleware($router);
        $this->configurePublishing();
    }

    public function registerCommands()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            XeroKeepAliveCommand::class,
        ]);
    }

    public function registerMiddleware($router)
    {
        //add middleware
        $router->aliasMiddleware('XeroAuthenticated', XeroAuthenticated::class);
    }

    public function configurePublishing()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/xero.php' => config_path('xero.php'),
        ], 'config');

        $timestamp = date('Y_m_d_His', time());

        $this->publishes([
            __DIR__.'/database/migrations/create_xero_tokens_table.php' => $this->app->databasePath()."/migrations/{$timestamp}_create_xero_tokens_table.php",
        ], 'migrations');
    }


    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/xero.php', 'xero');

        // Register the service the package provides.
        $this->app->singleton('xero', function ($app) {
            return new Xero;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['xero'];
    }
}
