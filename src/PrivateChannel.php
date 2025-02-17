<?php

declare(strict_types=1);

namespace LaravelHyperf\Broadcasting;

use LaravelHyperf\Broadcasting\Contracts\HasBroadcastChannel;

class PrivateChannel extends Channel
{
    /**
     * Create a new channel instance.
     */
    public function __construct(HasBroadcastChannel|string $name)
    {
        $name = $name instanceof HasBroadcastChannel ? $name->broadcastChannel() : $name;

        parent::__construct('private-' . $name);
    }
}
