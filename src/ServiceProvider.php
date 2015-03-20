<?php

namespace Overtrue\LaravelWechat;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Overtrue\Wechat\Wechat;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Boot the provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config.php' => config_path('wechat.php'),
        ], 'config');
    }

    /**
     * Register the provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('wechat', function($app)
        {
            return Wechat::make($app['config']->get('wechat', []));
        });
    }
}