<?php

namespace Overtrue\LaravelWeChat\Contracts;

interface Factory
{
    /**
     * Get a OfficialAccount by name.
     *
     * @param  string  $name
     * @return \EasyWeChat\OfficialAccount\Application
     */
    public function officialAccount($name = null);

    /**
     * Get a Work by name.
     *
     * @param  string  $name
     * @return \EasyWeChat\Work\AgentFactory
     */
    public function work($name = null);

    /**
     * Get a MiniProgram by name.
     *
     * @param  string  $name
     * @return \EasyWeChat\MiniProgram\Application
     */
    public function miniProgram($name = null);

    /**
     * Get a Payment by name.
     *
     * @param  string  $name
     * @return \EasyWeChat\Payment\Application
     */
    public function payment($name = null);

    /**
     * Get a OpenPlatform by name.
     *
     * @param  string  $name
     * @return \EasyWeChat\OpenPlatform\Application
     */
    public function openPlatform($name = null);
}
