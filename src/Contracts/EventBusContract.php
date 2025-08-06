<?php

namespace Crumbls\Tui\Contracts;

interface EventBusContract
{
    /**
     * Subscribe to an event
     */
    public function on(string $event, callable $listener): void;

    /**
     * Emit an event to all subscribers
     */
    public function emit(string $event, ...$args): void;

    /**
     * Remove a specific listener
     */
    public function off(string $event, callable $listener = null): void;

    /**
     * Remove all listeners for an event
     */
    public function removeAllListeners(string $event = null): void;

    /**
     * Get all registered listeners for an event
     */
    public function getListeners(string $event): array;

    /**
     * Check if an event has any listeners
     */
    public function hasListeners(string $event): bool;
}