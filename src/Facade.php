<?php

namespace Overtrue\LaravelWechat;

use Illuminate\Support\Facades\Facade as LaravelFacade;

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
