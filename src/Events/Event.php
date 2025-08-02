<?php

declare(strict_types=1);

namespace Crumbls\Tui\Events;

use Crumbls\Tui\Contracts\EventInterface;

/**
 * Base event class with common functionality.
 */
abstract class Event implements EventInterface
{
    protected float $timestamp;

    public function __construct()
    {
        $this->timestamp = microtime(true);
    }

    public function getType(): string
    {
        // Default to class name without namespace
        $className = static::class;
        $lastBackslash = strrpos($className, '\\');
        return $lastBackslash !== false ? substr($className, $lastBackslash + 1) : $className;
    }

    public function getTimestamp(): float
    {
        return $this->timestamp;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'timestamp' => $this->timestamp,
        ];
    }
}