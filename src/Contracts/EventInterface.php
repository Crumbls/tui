<?php

declare(strict_types=1);

namespace Crumbls\Tui\Contracts;

/**
 * Base interface for all TUI events.
 */
interface EventInterface
{
    /**
     * Get the event type/name.
     */
    public function getType(): string;

    /**
     * Get when the event occurred.
     */
    public function getTimestamp(): float;

    /**
     * Get event data as array.
     */
    public function toArray(): array;
}