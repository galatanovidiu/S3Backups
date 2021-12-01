<?php

namespace Galatanovidiu\S3Backups;

use Galatanovidiu\S3Backups\Commands\RunBackup;
use Illuminate\Support\ServiceProvider;

class S3BackupsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'galatanovidiu');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'galatanovidiu');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/s3-backups.php', 's3-backups');

        // Register the service the package provides.
        $this->app->singleton('s3-backups', function ($app) {
            return new S3Backups;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['s3-backups'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/s3-backups.php' => config_path('s3-backups.php'),
        ], 's3-backups.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/galatanovidiu'),
        ], 's3-backups.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/galatanovidiu'),
        ], 's3-backups.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/galatanovidiu'),
        ], 's3-backups.views');*/

        // Registering package commands.
         $this->commands([
            RunBackup::class
         ]);
    }
}
