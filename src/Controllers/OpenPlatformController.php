<?php

namespace Overtrue\LaravelWeChat\Controllers;

use EasyWeChat\OpenPlatform\Application;
use Overtrue\LaravelWeChat\Events\OpenPlatform as Events;

class OpenPlatformController extends Controller
{
    /**
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\BadRequestException
     * @throws \Throwable
     * @throws \ReflectionException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     */
    public function __invoke(Application $application): \Psr\Http\Message\ResponseInterface
    {
        $server = $application->getServer();

        $server->handleAuthorized(function ($payload) {
            event(new Events\Authorized($payload));
        });

        $server->handleUnauthorized(function ($payload) {
            event(new Events\Unauthorized($payload));
        });

        $server->handleAuthorizeUpdated(function ($payload) {
            event(new Events\UpdateAuthorized($payload));
        });

        $server->handleVerifyTicketRefreshed(function ($payload) {
            event(new Events\VerifyTicketRefreshed($payload));
        });

        return $server->serve();
    }
}
