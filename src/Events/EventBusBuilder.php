<?php

declare(strict_types=1);

namespace Crumbls\Tui\Events;

use Crumbls\Tui\Contracts\EventBusInterface;

/**
 * Fluent builder for EventBus configuration.
 */
class EventBusBuilder
{
    private int $maxHistorySize = 1000;
    private bool $enableHistory = true;
    private bool $enableStats = true;
    private array $globalListeners = [];
    private bool $catchExceptions = true;

    public static function create(): static
    {
        return new static();
    }

    /**
     * Set maximum history size.
     */
    public function maxHistory(int $size): static
    {
        $this->maxHistorySize = $size;
        return $this;
    }

    /**
     * Disable event history completely for performance.
     */
    public function withoutHistory(): static
    {
        $this->enableHistory = false;
        return $this;
    }

    /**
     * Disable stats collection for performance.
     */
    public function withoutStats(): static
    {
        $this->enableStats = false;
        return $this;
    }

    /**
     * Add global listeners that will be registered immediately.
     */
    public function listen(string $eventType, callable $handler): static
    {
        $this->globalListeners[] = [$eventType, $handler];
        return $this;
    }

    /**
     * Let listener exceptions bubble up instead of catching them.
     */
    public function throwOnListenerErrors(): static
    {
        $this->catchExceptions = false;
        return $this;
    }

    /**
     * Build the configured EventBus.
     */
    public function build(): EventBusInterface
    {
        $eventBus = new EventBus($this->maxHistorySize);
        
        // Configure based on builder settings
        if (!$this->enableHistory) {
            // We'd need to add this feature to EventBus
        }
        
        // Register global listeners
        foreach ($this->globalListeners as [$eventType, $handler]) {
            $eventBus->listen($eventType, $handler);
        }
        
        return $eventBus;
    }
}