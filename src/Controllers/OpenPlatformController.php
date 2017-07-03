<?php

namespace Overtrue\LaravelWechat\Controllers;

use Event;
use EasyWeChat\Foundation\Application;
use Overtrue\LaravelWechat\Events\OpenPlatform as Events;

class OpenPlatformController
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
        return $application->open_platform->server->setMessageHandler([$this, 'handle'])->serve();
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
