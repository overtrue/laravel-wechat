<?php

namespace Overtrue\LaravelWechat\Routing\Adapters;

class Lumen extends Adapter
{
    public function group(array $attributes, $callback)
    {
        $this->app->group($attributes, $callback);
    }

    public function any($uri, $action)
    {
        $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE'];

        $this->app->addRoute($verbs, $uri, $action);
    }
}
