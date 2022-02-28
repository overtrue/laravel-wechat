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

use EasyWeChat\MiniApp\Application as MiniApp;
use EasyWeChat\OfficialAccount\Application as OfficialAccount;
use EasyWeChat\OpenPlatform\Application as OpenPlatform;
use EasyWeChat\OpenWork\Application as OpenWork;
use EasyWeChat\Pay\Application as Payment;
use EasyWeChat\Work\Application as Work;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/../config/easywechat.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([$source => \config_path('easywechat.php')], 'laravel-wechat');
        }

        $this->mergeConfigFrom($source, 'easywechat');
    }

    public function register()
    {
        $this->setupConfig();

        $apps = [
            'official_account' => OfficialAccount::class,
            'work' => Work::class,
            'mini_app' => MiniApp::class,
            'pay' => Payment::class,
            'open_platform' => OpenPlatform::class,
            'open_work' => OpenWork::class,
        ];

        foreach ($apps as $name => $class) {
            if (empty(config('easywechat.'.$name))) {
                continue;
            }

            if ($config = config('easywechat.route.'.$name)) {
                Route::group($config['attributes'], function ($router) use ($config) {
                    $router->post($config['uri'], $config['action']);
                });
            }

            if (!empty(config('easywechat.'.$name.'.app_id')) || !empty(config('easywechat.'.$name.'.corp_id'))) {
                $accounts = [
                    'default' => config('easywechat.'.$name),
                ];
                config(['easywechat.'.$name.'.default' => $accounts['default']]);
            } else {
                $accounts = config('easywechat.'.$name);
            }

            foreach ($accounts as $account => $config) {
                $this->app->bind("wechat.{$name}.{$account}", function ($laravelApp) use ($name, $account, $config, $class) {
                    $app = new $class(array_merge(config('easywechat.defaults', []), $config));
                    if (config('easywechat.defaults.use_laravel_cache')) {
                        $app['cache'] = $laravelApp['cache.store'];
                    }
                    $app['request'] = $laravelApp['request'];

                    return $app;
                });
            }
            $this->app->alias("wechat.{$name}.default", 'easywechat.'.$name);
            $this->app->alias("wechat.{$name}.default", 'easywechat.'.$name);

            $this->app->alias('easywechat.'.$name, $class);
            $this->app->alias('easywechat.'.$name, $class);
        }
    }
}
