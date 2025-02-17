<?php

declare(strict_types=1);

namespace LaravelHyperf\Broadcasting;

use Hyperf\Collection\Arr;

trait InteractsWithBroadcasting
{
    /**
     * The broadcaster connection to use to broadcast the event.
     */
    protected array $broadcastConnection = [null];

    /**
     * Broadcast the event using a specific broadcaster.
     */
    public function broadcastVia(null|array|string $connection = null): static
    {
        $this->broadcastConnection = is_null($connection)
            ? [null]
            : Arr::wrap($connection);

        return $this;
    }

    /**
     * Get the broadcaster connections the event should be broadcast on.
     */
    public function broadcastConnections(): array
    {
        return $this->broadcastConnection;
    }
}
