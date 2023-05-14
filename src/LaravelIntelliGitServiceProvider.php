<?php

namespace Salehhashemi\LaravelIntelliGit;

use Illuminate\Support\ServiceProvider;

class LaravelIntelliGitServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/intelli-git.php' => config_path('intelli-git.php'),
            ], 'config');

            $this->commands([

            ]);
        }
    }
}
