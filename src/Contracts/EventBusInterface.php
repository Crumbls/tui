<?php

declare(strict_types=1);

namespace Crumbls\Tui\Contracts;

/**
 * Event bus for emitting and listening to events.
 */
interface EventBusInterface
{
    /**
     * Emit an event to all listeners.
     */
    public function emit(EventInterface $event): void;

    /**
     * Listen for events of a specific type.
     */
    public function listen(string $eventType, callable $handler): void;

    /**
     * Stop listening for events of a specific type.
     */
    public function unlisten(string $eventType, callable $handler): void;

    /**
     * Get all listeners for an event type.
     */
    public function getListeners(string $eventType): array;

    /**
     * Clear all listeners (useful for testing).
     */
    public function clearListeners(): void;
}