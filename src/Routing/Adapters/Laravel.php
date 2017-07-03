<?php

namespace Overtrue\LaravelWechat\Routing\Adapters;

class Laravel extends Adapter
{
    public function group(array $attributes, $callback)
    {
        $this->app->router->group($attributes, $callback);
    }

    public function any($uri, $action)
    {
        $this->app->router->any($uri, $action);
    }
}
