<?php

namespace Overtrue\LaravelWechat;

use EasyWeChat\Foundation\Application;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Laravel\Lumen\Application as LumenApplication;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * 延迟加载.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Boot the provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();
    }

    /**
     * Setup the config.
     *
     * @return void
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/config.php');

        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([
                $source => config_path('wechat.php'),
            ]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('wechat');
        }
        $this->mergeConfigFrom($source, 'wechat');
    }

    /**
     * Register the provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(['EasyWeChat\\Foundation\\Application' => 'wechat'], function ($app) {
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
