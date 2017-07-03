<?php

namespace Overtrue\LaravelWechat\Routing\Adapters;

use Illuminate\Container\Container;

abstract class Adapter
{
    /**
     * @var \Illuminate\Container\Container
     */
    protected $app;

    /**
     * Adapter constructor.
     *
     * @param \Illuminate\Container\Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    abstract public function group(array $attributes, $callback);

    abstract public function any($uri, $action);
}
