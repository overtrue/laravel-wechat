<?php

namespace Overtrue\LaravelWechat;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Overtrue\Wechat\Alias;

class ServiceProvider extends LaravelServiceProvider
{

    /**
     * 服务列表
     *
     * @var array
     */
    protected $services = [
        'wechat.user'      => 'WechatUser',
        'wechat.group'     => 'WechatGroup',
        'wechat.auth'      => 'WechatAuth',
        'wechat.menu'      => 'WechatMenu',
        'wechat.menu.item' => 'WechatMenuItem',
        'wechat.js'        => 'WechatJs',
        'wechat.staff'     => 'WechatStaff',
        'wechat.store'     => 'WechatStore',
        'wechat.card'      => 'WechatCard',
        'wechat.qrcode'    => 'WechatQRCode',
        'wechat.url'       => 'WechatUrl',
        'wechat.media'     => 'WechatMedia',
        'wechat.image'     => 'WechatImage',
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
        Alias::register();

        $this->app->singleton('wechat.server', function($app){
            return new WechatServer(config('wechat.appId'), config('wechat.token'), config('wechat.encodingAESKey'));
        });

        foreach ($this->services as $alias => $service) {
             $this->app->singleton($alias, function($app){
                return new {$service}(config('wechat.appId'), config('wechat.secret'));
            });
        }
    }
}