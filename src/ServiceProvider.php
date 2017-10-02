<?php

/*
 * This file is part of the overtrue/laravel-wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\LaravelWeChat;

use EasyWeChat\MiniProgram\Application as MiniProgram;
use EasyWeChat\OfficialAccount\Application as OfficialAccount;
use EasyWeChat\OpenPlatform\Application as OpenPlatform;
use EasyWeChat\Payment\Application as Payment;
use EasyWeChat\Work\AgentFactory as Work;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Overtrue\LaravelWeChat\Controllers\OpenPlatformController;
use Overtrue\Socialite\User as SocialiteUser;

/**
 * Class ServiceProvider.
 *
 * @author overtrue <i@overtrue.me>
 */
class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Boot the provider.
     */
    public function boot()
    {
        $this->setupConfig();

        if (config('wechat.route.enabled')) {
            $this->registerRoutes();
        }
    }

    /**
     * Setup the config.
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
     */
    public function register()
    {
        $this->setupConfig();
        
        $apps = [
            'official_account' => OfficialAccount::class,
            'work' => Work::class,
            'mini_program' => MiniProgram::class,
            'payment' => Payment::class,
            'open_platform' => OpenPlatform::class,
        ];

        foreach ($apps as $name => $class) {
            if (empty(config('wechat.'.$name))) {
                continue;
            }

            $this->app->singleton($class, function ($laravelApp) use ($name, $class) {
                $app = new $class(array_merge(config('wechat.defaults', []), config('wechat.'.$name)));
                if (config('wechat.use_laravel_cache')) {
                    $app['cache'] = $laravelApp['cache.store'];
                }
                $app['request'] = $laravelApp['request'];

                return $app;
            });
            $this->app->alias($class, 'wechat.'.$name);
            $this->app->alias($class, 'easywechat.'.$name);
        }
    }

    /**
     * Register routes.
     */
    protected function registerRoutes()
    {
        $router = $this->app instanceof LaravelApplication ? $this->app['router'] : $this->app;

        // Register open-platform routes...
        $router->group(config('wechat.route.open_platform.attributes', []), function ($router) {
            $router->post(config('wechat.route.open_platform.uri'), OpenPlatformController::class.'@index');
        });
    }

    /**
     * 创建模拟登录.
     */
    protected function setUpMockAuthUser()
    {
        $user = config('wechat.mock_user');

        if (is_array($user) && !empty($user['openid']) && config('wechat.enable_mock')) {
            $user = (new SocialiteUser([
                'id' => array_get($user, 'openid'),
                'name' => array_get($user, 'nickname'),
                'nickname' => array_get($user, 'nickname'),
                'avatar' => array_get($user, 'headimgurl'),
                'email' => null,
            ]))->merge(['original' => array_merge($user, ['privilege' => []])])->setProviderName('WeChat');

            session(['wechat.oauth_user' => $user]);
        }
    }
}
