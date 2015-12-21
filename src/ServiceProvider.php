<?php

namespace Overtrue\LaravelWechat;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Overtrue\Wechat\Server as WechatServer;
use Overtrue\Wechat\Alias;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * 延迟加载
     *
     * @var boolean
     */
    protected $defer = true;

    /**
     * 补充
     *
     * @var array
     */
    protected $providesAppends = ['wechat.server', 'Overtrue\\Wechat\\Server'];

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
        'wechat.notice'    => 'Overtrue\\Wechat\\Notice',
        'wechat.color'     => 'Overtrue\\Wechat\\Color',
        'wechat.semantic'  => 'Overtrue\\Wechat\\Semantic',
        'wechat.stats'     => 'Overtrue\\Wechat\\Stats',
        'wechat.broadcast' => 'Overtrue\\Wechat\\Broadcast',
    ];


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

        if (config('wechat.use_alias')) {
            Alias::register();
        }

        $this->app->singleton(['Overtrue\\Wechat\\Server' => 'wechat.server'], function($app){
            return new WechatServer(config('wechat.app_id'), config('wechat.token'), config('wechat.encoding_key'));
        });

        foreach ($this->services as $alias => $service) {
            $this->app->singleton([$service => $alias], function($app) use ($service){
                return new $service(config('wechat.app_id'), config('wechat.secret'));
            });
        }
    }

    /**
     * 提供的服务
     *
     * @return array
     */
    public function provides()
    {
        return array_merge(array_keys($this->services), array_values($this->services), $this->providesAppends);
    }
}
