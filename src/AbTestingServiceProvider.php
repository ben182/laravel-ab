<?php

namespace Ben182\AbTesting;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Ben182\AbTesting\Commands\FlushCommand;
use Ben182\AbTesting\Commands\ReportCommand;

class AbTestingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-ab');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-ab');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('ab-testing.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-ab'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/laravel-ab'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-ab'),
            ], 'lang');*/

            // Registering package commands.
            $this->commands([
                ReportCommand::class,
                FlushCommand::class,
            ]);
        }

        Request::macro('abTest', function () {
            return app(AbTesting::class)->getExperiment();
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'ab-testing');

        // Register the main class to use with the facade
        $this->app->singleton('ab-testing', function () {
            return new AbTesting;
        });
    }
}
