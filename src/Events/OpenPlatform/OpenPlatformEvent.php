<?php

namespace Overtrue\LaravelWeChat\Events\OpenPlatform;

abstract class OpenPlatformEvent
{
    public function __construct(public array $payload)
    {
    }

    public function __call($name, $args)
    {
        return $this->payload[substr($name, 3)] ?? null;
    }
}
