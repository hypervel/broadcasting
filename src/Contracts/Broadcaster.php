<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Broadcasting\Contracts;

use Hyperf\HttpServer\Contract\RequestInterface;

interface Broadcaster
{
    /**
     * Authenticate the incoming request for a given channel.
     */
    public function auth(RequestInterface $request): mixed;

    /**
     * Return the valid authentication response.
     */
    public function validAuthenticationResponse(RequestInterface $request, mixed $result): mixed;

    /**
     * Broadcast the given event.
     *
     * @throws \Illuminate\Broadcasting\BroadcastException
     */
    public function broadcast(array $channels, string $event, array $payload = []): void;
}
