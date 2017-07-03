<?php

namespace Overtrue\LaravelWechat\Routing;

use Illuminate\Container\Container;
use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;

class Router
{
    /**
     * Routing adapter instance.
     *
     * @var \Overtrue\LaravelWechat\Routing\Adapters\Adapter
     */
    protected $adapter;

    /**
     * Create a new route registrar instance.
     *
     * @param \Illuminate\Container\Container $app
     */
    public function __construct(Container $app)
    {
        if ($app instanceof LaravelApplication) {
            $this->adapter = new Adapters\Laravel($app);
        } elseif ($app instanceof LumenApplication) {
            $this->adapter = new Adapters\Lumen($app);
        }
    }

    /**
     * @param string $method
     * @param array  $arguments
     */
    public function __call($method, $arguments)
    {
        call_user_func_array([$this->adapter, $method], $arguments);
    }
}
