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
    static public function getFacadeAccessor()
    {
        return "wechat";
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
        $app = static::getFacadeRoot();

        if (method_exists($app, $name)) {
            return call_user_func_array([$app, $name], $args);
        }

        return $app->$name;
    }
}