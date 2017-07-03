<?php

namespace Overtrue\LaravelWechat;

use EasyWeChat\Foundation\Application as EasyWeChat;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Overtrue\LaravelWechat\Routing\Router;
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

        if ($this->config('route.enabled')) {
            $this->registerRoutes();
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
        $this->app->singleton(EasyWeChat::class, function ($app) {
            $easywechat = new EasyWeChat(config('wechat'));
            if (config('wechat.use_laravel_cache')) {
                $easywechat->cache = new CacheBridge();
            }
            $easywechat->server->setRequest($app['request']);

            return $easywechat;
        });

        $this->app->alias(EasyWeChat::class, 'wechat');
        $this->app->alias(EasyWeChat::class, 'easywechat');
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

    /**
     * Register routes.
     */
    protected function registerRoutes()
    {
        $router = new Router($this->app);

        $router->group($this->routeAttributes(), function () use ($router) {
            $router->any($this->config('route.open_platform_serve_url'), 'OpenPlatformController@index');
        });
    }


    /**
     * Get Route attributes.
     *
     * @return array
     */
    public function routeAttributes()
    {
        return array_merge($this->config('route.attributes', []), [
            'namespace' => '\\Overtrue\\LaravelWechat\\Controllers',
        ]);
    }

    /**
     * Get config value by key.
     *
     * @param string $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    private function config($key, $default = null)
    {
        return $this->app->make('config')->get("wechat.{$key}", $default);
    }
}
