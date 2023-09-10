<?php

/*
 * This file is part of the Fireflies package.
 *
 * (c) Haruna Ahmadu <akhmadharuna@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sleemkeen\Fireflies;

use Illuminate\Support\ServiceProvider;

class FirefliesServiceProvider extends ServiceProvider
{
    /*
    * Indicates if loading of the provider is deferred.
    *
    * @var bool
    */
    protected $defer = false;
    
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('laravel-fireflies', function () {
            return new Fireflies;
        });

        $this->mergeConfigFrom(
            __DIR__.'/../config/fireflies.php', 'fireflies'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // $config = realpath(__DIR__.'/../resources/config/fireflies.php');
        // $this->publishes([
        //     $config => config_path('fireflies.php')
        // ]);

        $this->publishes([
            __DIR__.'/../config/fireflies.php' => config_path('fireflies.php'),
        ]);
    }

    /**
    * Get the services provided by the provider
    * @return array
    */
    public function provides()
    {
        return ['laravel-fireflies'];
    }
}
