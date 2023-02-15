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

            if (! empty(config('easywechat.'.$name.'.app_id')) || ! empty(config('easywechat.'.$name.'.corp_id'))) {
                $accounts = [
                    'default' => config('easywechat.'.$name),
                ];
                config(['easywechat.'.$name.'.default' => $accounts['default']]);
            } else {
                $accounts = config('easywechat.'.$name);
            }

            foreach ($accounts as $account => $config) {
                $this->app->bind("easywechat.{$name}.{$account}", function ($laravelApp) use ($config, $class) {
                    $app = new $class(array_merge(config('easywechat.defaults', []), $config));

                    if (\is_callable([$app, 'setCache'])) {
                        $app->setCache($laravelApp['cache.store']);
                    }

                    if (\is_callable([$app, 'setRequestFromSymfonyRequest'])) {
                        $app->setRequestFromSymfonyRequest($laravelApp['request']);
                    }

                    return $app;
                });
            }
            $this->app->alias("easywechat.{$name}.default", 'easywechat.'.$name);
            $this->app->alias('easywechat.'.$name, $class);
        }
    }
}
