<?php

namespace Laravelddd\Domain;

use Illuminate\Support\ServiceProvider;
use Laravelddd\Domain\Commands\MakeDddDomain;

class LaravelDddDomainServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            // Register commands
            $this->commands([
                MakeDddDomain::class,
            ]);
            
            // Publish configuration
            $this->publishes([
                __DIR__ . '/../config/laravelddd-domain.php' => config_path('laravelddd-domain.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravelddd-domain.php',
            'laravelddd-domain'
        );
    }
}