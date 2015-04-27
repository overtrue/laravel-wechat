<?php
namespace Overtrue\LaravelWechat;

use Illuminate\Support\Facades\Facade as LaravelFacade;

class Facade extends LaravelFacade
{
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