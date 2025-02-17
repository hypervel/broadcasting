<?php

declare(strict_types=1);

namespace LaravelHyperf\Broadcasting\Contracts;

interface Factory
{
    /**
     * Get a broadcaster implementation by name.
     */
    public function connection(?string $name = null): Broadcaster;
}
