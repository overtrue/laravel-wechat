<?php

namespace Overtrue\LaravelWechat;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Overtrue\Wechat\Alias;

class ServiceProvider extends LaravelServiceProvider
{
    protected $defer = true;

    /**
     * 服务列表
     *
     * @var array
     */
    protected $services = [
        'wechat.user'      => 'Overtrue\\Wechat\\User',
        'wechat.group'     => 'Overtrue\\Wechat\\Group',
        'wechat.auth'      => 'Overtrue\\Wechat\\Auth',
        'wechat.menu'      => 'Overtrue\\Wechat\\Menu',
        'wechat.menu.item' => 'Overtrue\\Wechat\\MenuItem',
        'wechat.js'        => 'Overtrue\\Wechat\\Js',
        'wechat.staff'     => 'Overtrue\\Wechat\\Staff',
        'wechat.store'     => 'Overtrue\\Wechat\\Store',
        'wechat.card'      => 'Overtrue\\Wechat\\Card',
        'wechat.qrcode'    => 'Overtrue\\Wechat\\QRCode',
        'wechat.url'       => 'Overtrue\\Wechat\\Url',
        'wechat.media'     => 'Overtrue\\Wechat\\Media',
        'wechat.image'     => 'Overtrue\\Wechat\\Image',
    ];

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
        if (config('wechat.alias')) {
            Alias::register();
        }

        $this->app->singleton('wechat.server', function($app){
            return new WechatServer(config('wechat.appId'), config('wechat.token'), config('wechat.encodingAESKey'));
        });

        foreach ($this->services as $alias => $service) {
            $this->app->singleton($service, function($app){
                return new $service(config('wechat.appId'), config('wechat.secret'));
            });

            $this->app->alias($service, $alias);
        }
    }

    /**
     * 提供的服务名称列表
     *
     * @return array
     */
    public function provides()
    {
        return array_keys($this->services) + array_values($this->services);
    }
}