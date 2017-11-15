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

use Overtrue\LaravelWeChat\Contracts\Factory;

use EasyWeChat\MiniProgram\Application as MiniProgram;
use EasyWeChat\OfficialAccount\Application as OfficialAccount;
use EasyWeChat\OpenPlatform\Application as OpenPlatform;
use EasyWeChat\Payment\Application as Payment;
use EasyWeChat\Work\AgentFactory as Work;

class WeChatManager implements Factory
{
    /**
     * LaravelApp.
     */
    protected $laravelApp;

    /**
     *  collections of types.
     */
    protected $officialAccounts;
    protected $works;
    protected $miniPrograms;
    protected $payments;
    protected $openPlatforms;

    public function __construct($app)
    {
        $this->laravelApp = $app;
    }

    /**
     * Get a OfficialAccount by name.
     *
     * @param string $name
     *
     * @return \EasyWeChat\OfficialAccount\Application
     */
    public function officialAccount($name = null)
    {
        return $this->resolve('official_account', $name);
    }

    /**
     * Get a Work by name.
     *
     * @param string $name
     *
     * @return \EasyWeChat\Work\AgentFactory
     */
    public function work($name = null)
    {
        return $this->resolve('work', $name);
    }

    /**
     * Get a MiniProgram by name.
     *
     * @param string $name
     *
     * @return \EasyWeChat\MiniProgram\Application
     */
    public function miniProgram($name = null)
    {
        return $this->resolve('mini_program', $name);
    }

    /**
     * Get a Payment by name.
     *
     * @param string $name
     *
     * @return \EasyWeChat\Payment\Application
     */
    public function payment($name = null)
    {
        return $this->resolve('payment', $name);
    }

    /**
     * Get a OpenPlatform by name.
     *
     * @param string $name
     *
     * @return \EasyWeChat\OpenPlatform\Application
     */
    public function openPlatform($name = null)
    {
        return $this->resolve('open_platform', $name);
    }

    /**
     * Maker.
     */
    protected function resolve($type, $name)
    {
        $name = $name ?: 'default';

        if (isset($this->{$type}[$name])) {
            return $this->{$type}[$name];
        }

        $apps = [
            'official_account' => OfficialAccount::class,
            'work' => Work::class,
            'mini_program' => MiniProgram::class,
            'payment' => Payment::class,
            'open_platform' => OpenPlatform::class,
        ];

        $app = new $apps[$type](array_merge(config('wechat.defaults', []), config("wechat.{$type}.{$name}")));
        if (config('wechat.defaults.use_laravel_cache')) {
            $app['cache'] = $this->laravelApp['cache.store'];
        }
        $app['request'] = $this->laravelApp['request'];

        return $this->{$type}[$name] = $app;
    }
}