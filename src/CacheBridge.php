<?php

/*
 * This file is part of the overtrue/laravel-wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\LaravelWeChat;

use Illuminate\Cache\Repository;
use Psr\SimpleCache\CacheInterface;

class CacheBridge implements CacheInterface
{
    /**
     * @var \Illuminate\Cache\Repository
     */
    protected $repository;

    /**
     * @param \Illuminate\Cache\Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function get($key, $default = null)
    {
        return $this->repository->get($key, $default);
    }

    public function set($key, $value, $ttl = null)
    {
        return $this->repository->put($key, $value, $this->toMinutes($ttl));
    }

    public function delete($key)
    {
    }

    public function clear()
    {
    }

    public function getMultiple($keys, $default = null)
    {
    }

    public function setMultiple($values, $ttl = null)
    {
    }

    public function deleteMultiple($keys)
    {
    }

    public function has($key)
    {
        return $this->repository->has($key);
    }

    protected function toMinutes($ttl = null)
    {
        if (!is_null($ttl)) {
            return $ttl / 60;
        }
    }
}
