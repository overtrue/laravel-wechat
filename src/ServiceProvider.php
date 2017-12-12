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

use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Overtrue\Socialite\User as SocialiteUser;

/**
 * Class ServiceProvider.
 *
 * @author overtrue <i@overtrue.me>
 */
class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Boot the provider.
     */
    public function boot()
    {
        if ($this->app instanceof LaravelApplication) {
            // 创建模拟授权
            $this->setUpMockAuthUser();
        }
    }

    /**
     * Setup the config.
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/config.php');

        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('wechat.php')]);
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

        $this->app->singleton('wechat', function ($laravelApp) {
            return new WeChatManager($laravelApp);
        });
    }

    protected function getRouter()
    {
        if ($this->app instanceof LumenApplication && !class_exists('Laravel\Lumen\Routing\Router')) {
            return $this->app;
        }

        return $this->app->router;
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
