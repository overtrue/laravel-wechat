<?php
namespace Overtrue\LaravelWechat;

use Illuminate\Support\Facades\Facade as LaravelFacade;

class Facade extends LaravelFacade
{
    /**
     * 默认为 Server
     *
     * @return string
     */
    public function getFacadeAccessor()
    {
        return "wechat.server";
    }

    /**
     * 获取微信 SDK 服务
     *
     * @param string $name
     * @param array  $args
     *
     * @return mixed
     */
    static public function __callStatic($name, $args)
    {
        return self::resolveFacadeInstance("wechat.{$name}");
    }
}