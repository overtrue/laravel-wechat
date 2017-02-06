<?php

namespace Overtrue\LaravelWechat\Controllers;

use EasyWeChat\Foundation\Application;
use EasyWeChat\Support\Arr;
use Illuminate\Support\Facades\Event;
use App\Http\Controllers\Controller as LaravelController;
use Overtrue\LaravelWechat\Events\OpenPlatform as Events;

class EasyWeChatController extends LaravelController
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
     * Handle request and fire events.
     *
     * @return string
     */
    public function openPlatformServe()
    {
        $app = new Application(config('wechat'));

        $server = $app->open_platform->server;

        list($name, $event) = $server->listServe();

        if ($class = Arr::get($this->events, $name)) {
            Event::fire(new $class($event));
        }

        return 'success';
    }
}
