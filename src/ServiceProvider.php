<?php

namespace Overtrue\LaravelWechat;

use EasyWeChat\Foundation\Application as EasyWeChatApplication;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Overtrue\LaravelWechat\Providers\RouteServiceProvider;
use Overtrue\Socialite\User as SocialiteUser;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Boot the provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();

        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Setup the config.
     *
     * @return void
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/config.php');

        if ($this->app instanceof LaravelApplication) {
            if ($this->app->runningInConsole()) {
                $this->publishes([
                    $source => config_path('wechat.php'),
                ]);
            }

            // 创建模拟授权
            $this->setUpMockAuthUser();
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
        $this->app->singleton(EasyWeChatApplication::class, function ($laravelApp) {
            $app = new EasyWeChatApplication(config('wechat'));
            if (config('wechat.use_laravel_cache')) {
                $app->cache = new CacheBridge();
            }
            $app->server->setRequest($laravelApp['request']);

            return $app;
        });

        $this->app->alias(EasyWeChatApplication::class, 'wechat');
        $this->app->alias(EasyWeChatApplication::class, 'easywechat');
    }

    /**
     * 创建模拟登录.
     */
    protected function setUpMockAuthUser()
    {
        $user = config('wechat.mock_user');

        if (is_array($user) && !empty($user['openid']) && config('wechat.enable_mock')) {
            $user = new SocialiteUser([
                'id'       => array_get($user, 'openid'),
                'name'     => array_get($user, 'nickname'),
                'nickname' => array_get($user, 'nickname'),
                'avatar'   => array_get($user, 'headimgurl'),
                'email'    => null,
                'original' => array_merge($user, ['privilege' => []]),
            ]);

            session(['wechat.oauth_user' => $user]);
        }
    }
}
