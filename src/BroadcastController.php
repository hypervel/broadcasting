<?php

declare(strict_types=1);

namespace LaravelHyperf\Broadcasting;

use Hyperf\HttpServer\Contract\RequestInterface;
use LaravelHyperf\HttpMessage\Exceptions\AccessDeniedHttpException;
use LaravelHyperf\Support\Facades\Broadcast;

class BroadcastController
{
    /**
     * Authenticate the request for channel access.
     */
    public function authenticate(RequestInterface $request): mixed
    {
        return Broadcast::auth($request);
    }

    /**
     * Authenticate the current user.
     *
     * See: https://pusher.com/docs/channels/server_api/authenticating-users/#user-authentication.
     *
     * @throws AccessDeniedHttpException
     */
    public function authenticateUser(RequestInterface $request): array
    {
        return Broadcast::resolveAuthenticatedUser($request) ?? throw new AccessDeniedHttpException();
    }
}
