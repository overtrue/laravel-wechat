<?php

namespace Overtrue\LaravelWeChat\Traits;

use EasyWeChat\OpenPlatform\Application;
use Overtrue\LaravelWeChat\Events\OpenPlatform\Authorized;
use Overtrue\LaravelWeChat\Events\OpenPlatform\AuthorizeUpdated;
use Overtrue\LaravelWeChat\Events\OpenPlatform\Unauthorized;
use Overtrue\LaravelWeChat\Events\OpenPlatform\VerifyTicketRefreshed;

trait HandleOpenPlatformServerEvents
{
    /**
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\BadRequestException
     * @throws \Throwable
     * @throws \ReflectionException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     */
    public function handleServerEvents(Application $application, ?callable $callback = null): \Psr\Http\Message\ResponseInterface
    {
        $this->disableLaravelDebugbar();

        $server = $application->getServer();

        $server->handleAuthorized(function ($payload) {
            event(new Authorized($payload->toArray()));
        });

        $server->handleUnauthorized(function ($payload) {
            event(new Unauthorized($payload->toArray()));
        });

        $server->handleAuthorizeUpdated(function ($payload) {
            event(new AuthorizeUpdated($payload->toArray()));
        });

        $server->handleVerifyTicketRefreshed(function ($payload) {
            event(new VerifyTicketRefreshed($payload->toArray()));
        });

        if ($callback) {
            $callback($server);
        }

        return $server->serve();
    }

    protected function disableLaravelDebugbar(): void
    {
        $debugbar = 'Barryvdh\Debugbar\LaravelDebugbar';

        if (class_exists($debugbar)) {
            try {
                resolve($debugbar)->disable();
            } catch (\Throwable) {
                //
            }
        }
    }
}
