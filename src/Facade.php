<?php

namespace Overtrue\LaravelWechat;

use Illuminate\Support\Facades\Facade as LaravelFacade;
use Log;

/**
 * @see Overtrue\Wechat\Wechat
 */
class Facade extends LaravelFacade
{
    /**
     * 默认提供服务端
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'wechat.server';
    }

    /**
     * 魔术方法
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (isset(static::$app["wechat.{$method}"])) {
            Log::warning("建议不要再统一使用 `Wechat::on()` 等形式使用，overtrue/wechat 2.x 已经改为独立服务类型来使用各模块的功能，更多请参阅文档：https://github.com/overtrue/wechat/wiki");
            return static::$app["wechat.{$method}"];
        }

        parent::__call($method, $args);
    }
}