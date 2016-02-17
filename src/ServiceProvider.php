<?php

namespace Overtrue\LaravelWechat;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use EasyWeChat\Foundation\Application;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * 延迟加载
     *
     * @var boolean
     */
    protected $defer = true;

    /**
     * Boot the provider.
     *
     * @return void
     */
    public function boot()
    {
        if (function_exists('config_path')) {
            $this->publishes([
                __DIR__ . '/config.php' => config_path('wechat.php'),
            ], 'config');
        }
    }

    /**
     * Register the provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config.php', 'wechat'
        );

        $this->app->singleton(['EasyWeChat\\Foundation\\Application' => 'wechat'], function($app){
            $app = new Application(config('wechat'));

            if (config('wechat.use_laravel_cache')) {
                $app->cache = new CacheBridge();
            }

            return $app;
        });
    }

    /**
     * 提供的服务
     *
     * @return array
     */
    public function provides()
    {
        return ['wechat', 'EasyWeChat\\Foundation\\Application'];
    }
}