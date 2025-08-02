<?php

declare(strict_types=1);

namespace Crumbls\Tui\Events;

use Crumbls\Tui\Contracts\EventBusInterface;
use Crumbls\Tui\Contracts\EventInterface;

/**
 * Simple in-memory event bus implementation.
 */
class EventBus implements EventBusInterface
{
    /** @var array<string, callable[]> */
    private array $listeners = [];

    /** @var EventInterface[] */
    private array $eventHistory = [];

    private int $maxHistorySize;

    public function __construct(int $maxHistorySize = 1000)
    {
        $this->maxHistorySize = $maxHistorySize;
    }

    public function emit(EventInterface $event): void
    {
        // Add to history
        $this->eventHistory[] = $event;
        
        // Trim history if it gets too large
        if (count($this->eventHistory) > $this->maxHistorySize) {
            array_shift($this->eventHistory);
        }

        // Call all listeners for this event type
        $eventType = $event->getType();
        if (isset($this->listeners[$eventType])) {
            foreach ($this->listeners[$eventType] as $handler) {
                try {
                    $handler($event);
                } catch (\Throwable $e) {
                    // Log error but don't let one bad listener break others
                    // In a real implementation, you might want proper logging here
                    error_log("Event handler error: " . $e->getMessage());
                }
            }
        }
    }

    public function listen(string $eventType, callable $handler): void
    {
        if (!isset($this->listeners[$eventType])) {
            $this->listeners[$eventType] = [];
        }
        
        $this->listeners[$eventType][] = $handler;
    }

    public function unlisten(string $eventType, callable $handler): void
    {
        if (!isset($this->listeners[$eventType])) {
            return;
        }

        $this->listeners[$eventType] = array_filter(
            $this->listeners[$eventType],
            fn($listener) => $listener !== $handler
        );

        // Clean up empty arrays
        if (empty($this->listeners[$eventType])) {
            unset($this->listeners[$eventType]);
        }
    }

    public function getListeners(string $eventType): array
    {
        return $this->listeners[$eventType] ?? [];
    }

    public function clearListeners(): void
    {
        $this->listeners = [];
    }

    /**
     * Get event history (useful for debugging).
     */
    public function getEventHistory(): array
    {
        return $this->eventHistory;
    }

    /**
     * Query event history with Eloquent-style syntax.
     */
    public function query(): EventQuery
    {
        return new EventQuery($this->eventHistory);
    }

    /**
     * Get events of a specific type (shorthand).
     */
    public function eventsOfType(string $type): EventQuery
    {
        return $this->query()->whereType($type);
    }

    /**
     * Get recent events (shorthand).
     */
    public function recent(int $count = 10): array
    {
        return $this->query()->latest()->take($count)->get();
    }

    /**
     * Clear event history.
     */
    public function clearHistory(): void
    {
        $this->eventHistory = [];
    }

    /**
     * Get statistics about the event bus.
     */
    public function getStats(): array
    {
        $listenerCounts = [];
        foreach ($this->listeners as $eventType => $handlers) {
            $listenerCounts[$eventType] = count($handlers);
        }

        return [
            'total_event_types' => count($this->listeners),
            'total_listeners' => array_sum($listenerCounts),
            'events_in_history' => count($this->eventHistory),
            'listener_counts' => $listenerCounts,
        ];
    }
}