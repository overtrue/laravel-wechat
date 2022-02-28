<?php

namespace Overtrue\LaravelWeChat;

use Illuminate\Support\Facades\Facade;

class EasyWeChat extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'wechat.official_account';
    }

    public static function officialAccount(string $name = 'default'): \EasyWeChat\OfficialAccount\Application
    {
        return app('wechat.official_account.'.$name);
    }

    public static function work(string $name = 'default'): \EasyWeChat\Work\Application
    {
        return app('wechat.work.'.$name);
    }


    public static function payment(string $name = 'default'): \EasyWeChat\Pay\Application
    {
        return app('wechat.payment.'.$name);
    }

    public static function miniApp(string $name = 'default'): \EasyWeChat\MiniApp\Application
    {
        return app('wechat.mini_program.'.$name);
    }

    public static function openPlatform(string $name = 'default'): \EasyWeChat\OpenPlatform\Application
    {
        return app('wechat.open_platform.'.$name);
    }
}
