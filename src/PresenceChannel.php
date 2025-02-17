<?php

declare(strict_types=1);

namespace LaravelHyperf\Broadcasting;

class PresenceChannel extends Channel
{
    /**
     * Create a new channel instance.
     */
    public function __construct(string $name)
    {
        parent::__construct('presence-' . $name);
    }
}
