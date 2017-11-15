<?php

/*
 * This file is part of the overtrue/laravel-wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\LaravelWeChat\Contracts;

interface Factory
{
    /**
     * Get a OfficialAccount by name.
     *
     * @param string $name
     * @return \EasyWeChat\OfficialAccount\Application
     */
    public function officialAccount($name = null);

    /**
     * Get a Work by name.
     *
     * @param string $name
     * @return \EasyWeChat\Work\AgentFactory
     */
    public function work($name = null);

    /**
     * Get a MiniProgram by name.
     *
     * @param string $name
     * @return \EasyWeChat\MiniProgram\Application
     */
    public function miniProgram($name = null);

    /**
     * Get a Payment by name.
     *
     * @param string $name
     * @return \EasyWeChat\Payment\Application
     */
    public function payment($name = null);

    /**
     * Get a OpenPlatform by name.
     *
     * @param string $name
     * @return \EasyWeChat\OpenPlatform\Application
     */
    public function openPlatform($name = null);
}
