<?php

namespace Overtrue\LaravelWechat;

use EasyWeChat\Foundation\Application as EasyWeChatApplication;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Overtrue\LaravelWechat\ServiceProviders\RouteServiceProvider;
use Overtrue\Socialite\User as SocialiteUser;

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

        if ($this->isEnableOpenPlatform()) {
            $this->app->register(RouteServiceProvider::class);
        }
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
     * 提供的服务
     *
     * @return array
     */
    public function provides()
    {
        return ['wechat', EasyWeChatApplication::class];
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
                'original' => array_merge($user, [privilege => []]),
            ]);

            session(['wechat.oauth_user' => $user]);
        }
    }

    /**
     * Check open platform is configured.
     *
     * @return bool
     */
    private function isEnableOpenPlatform()
    {
        return $this->config()->has('wechat.open_platform');
    }

    /**
     * Get config value by key
     *
     * @return \Illuminate\Config\Repository
     */
    private function config()
    {
        return $this->app['config'];
    }
}
