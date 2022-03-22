<?php

namespace Overtrue\LaravelWeChat;

use Illuminate\Support\Facades\Facade;

class EasyWeChat extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'easywechat.official_account';
    }

    public static function officialAccount(string $name = 'default'): \EasyWeChat\OfficialAccount\Application
    {
        return app('easywechat.official_account.'.$name);
    }

    public static function work(string $name = 'default'): \EasyWeChat\Work\Application
    {
        return app('easywechat.work.'.$name);
    }

    public static function openWork(string $name = 'default'): \EasyWeChat\OpenWork\Application
    {
        return app('easywechat.open_work.'.$name);
    }

    public static function pay(string $name = 'default'): \EasyWeChat\Pay\Application
    {
        return app('easywechat.pay.'.$name);
    }

    public static function miniApp(string $name = 'default'): \EasyWeChat\MiniApp\Application
    {
        return app('easywechat.mini_app.'.$name);
    }

    public static function openPlatform(string $name = 'default'): \EasyWeChat\OpenPlatform\Application
    {
        return app('easywechat.open_platform.'.$name);
    }
}
