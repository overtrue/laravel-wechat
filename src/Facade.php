<?php

/*
 * This file is part of the overtrue/laravel-wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\LaravelWechat;

use Illuminate\Support\Facades\Facade as LaravelFacade;

/**
 * Class Facade.
 *
 * @author overtrue <i@overtrue.me>
 */
class Facade extends LaravelFacade
{
    /**
     * 默认为 Server.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'wechat.official_account';
    }

    /**
     * @return \EasyWeChat\OfficialAccount\Application
     */
    public function officialAccount()
    {
        return app('wechat.official_account');
    }

    /**
     * @return \EasyWeChat\WeWork\AgentFactory
     */
    public function weWork()
    {
        return app('wechat.we_work');
    }

    /**
     * @return \EasyWeChat\Payment\Application
     */
    public function payment()
    {
        return app('wechat.payment');
    }

    /**
     * @return \EasyWeChat\MiniProgram\Application
     */
    public function miniProgram()
    {
        return app('wechat.mini_grogram');
    }

    /**
     * @return \EasyWeChat\OpenPlatform\Application
     */
    public function openPlatform()
    {
        return app('wechat.open_platform');
    }
}
