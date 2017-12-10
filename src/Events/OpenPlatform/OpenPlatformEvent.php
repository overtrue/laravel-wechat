<?php

/*
 * This file is part of the overtrue/laravel-wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\LaravelWeChat\Events\OpenPlatform;

abstract class OpenPlatformEvent
{
    /**
     * @var array
     */
    public $payload;

    /**
     * Create a new event instance.
     *
     * @param array $payload
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function __call($name, $args)
    {
        return $this->payload[substr($name, 3)] ?? null;
    }
}
