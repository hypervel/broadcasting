<?php

declare(strict_types=1);

namespace LaravelHyperf\Broadcasting\Broadcasters;

use Hyperf\Collection\Arr;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Pool\Exception\ConnectionException;
use Hyperf\Redis\RedisFactory;
use LaravelHyperf\Broadcasting\BroadcastException;
use LaravelHyperf\HttpMessage\Exceptions\AccessDeniedHttpException;
use Psr\Container\ContainerInterface;
use RedisException;

class RedisBroadcaster extends Broadcaster
{
    use UsePusherChannelConventions;

    /**
     * Create a new broadcaster instance.
     */
    public function __construct(
        protected ContainerInterface $container,
        protected RedisFactory $factory,
        protected ?string $connection = null,
        protected string $prefix = ''
    ) {
    }

    /**
     * Authenticate the incoming request for a given channel.
     *
     * @throws AccessDeniedHttpException
     */
    public function auth(RequestInterface $request): mixed
    {
        $channelName = $request->input('channel_name');
        $normalizeChannelName = $this->normalizeChannelName(
            str_replace($this->prefix, '', $channelName)
        );

        if (empty($channelName)
            || ($this->isGuardedChannel($channelName) && ! $this->retrieveUser($normalizeChannelName))
        ) {
            throw new AccessDeniedHttpException();
        }

        return parent::verifyUserCanAccessChannel(
            $request,
            $normalizeChannelName
        );
    }

    /**
     * Return the valid authentication response.
     */
    public function validAuthenticationResponse(RequestInterface $request, mixed $result): mixed
    {
        if (is_bool($result)) {
            return json_encode($result);
        }

        $channelName = $this->normalizeChannelName($request->input('channel_name'));

        $user = $this->retrieveUser($channelName);

        $broadcastIdentifier = method_exists($user, 'getAuthIdentifierForBroadcasting')
                        ? $user->getAuthIdentifierForBroadcasting()
                        : $user->getAuthIdentifier();

        return json_encode(['channel_data' => [
            'user_id' => $broadcastIdentifier,
            'user_info' => $result,
        ]]);
    }

    /**
     * Broadcast the given event.
     *
     * @throws BroadcastException
     */
    public function broadcast(array $channels, string $event, array $payload = []): void
    {
        if (empty($channels)) {
            return;
        }

        $connection = $this->factory->get($this->connection);

        $payload = json_encode([
            'event' => $event,
            'data' => $payload,
            'socket' => Arr::pull($payload, 'socket'),
        ]);

        try {
            $connection->eval(
                $this->broadcastMultipleChannelsScript(),
                [$payload, ...$this->formatChannels($channels)],
                0,
            );
        } catch (ConnectionException|RedisException $e) {
            throw new BroadcastException(
                sprintf('Redis error: %s.', $e->getMessage())
            );
        }
    }

    /**
     * Get the Lua script for broadcasting to multiple channels.
     *
     * ARGV[1] - The payload
     * ARGV[2...] - The channels
     */
    protected function broadcastMultipleChannelsScript(): string
    {
        return <<<'LUA'
            for i = 2, #ARGV do
              redis.call('publish', ARGV[i], ARGV[1])
            end
        LUA;
    }

    /**
     * Format the channel array into an array of strings.
     */
    protected function formatChannels(array $channels): array
    {
        return array_map(function ($channel) {
            return $this->prefix . $channel;
        }, parent::formatChannels($channels));
    }
}
