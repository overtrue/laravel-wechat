<?php

/*
 * This file is part of the overtrue/easy-sms.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\LaravelWechat\Controllers;

use Event;
use EasyWeChat\Foundation\Application;
use Illuminate\Routing\Controller;
use Overtrue\LaravelWechat\Events\OpenPlatform as Events;

class OpenPlatformController extends Controller
{
    /**
     * Events.
     *
     * @var array
     */
    protected $events = [
        'authorized' => Events\Authorized::class,
        'unauthorized' => Events\Unauthorized::class,
        'updateauthorized' => Events\UpdateAuthorized::class,
    ];

    /**
     * Register for open platform.
     *
     * @param \EasyWeChat\Foundation\Application $application
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $application)
    {
        $server = $application->open_platform->server;

        $server->setMessageHandler([$this, 'handle']);

        return $server->serve();
    }

    /**
     * Handle event message and fire event.
     *
     * @param \EasyWeChat\Support\Collection $message
     */
    public function handle($message)
    {
        if ($event = array_get($this->events, $message->InfoType)) {
            Event::fire(new $event($message));
        }
    }
}
