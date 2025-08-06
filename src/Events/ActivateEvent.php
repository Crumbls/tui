<?php

namespace Crumbls\Tui\Events;

use Crumbls\Tui\Components\Contracts\Component;

class ActivateEvent
{
    public function __construct(
        public readonly Component $component,
        public readonly string $trigger = 'enter'
    ) {
    }

    /**
     * Check if activation was triggered by a specific key
     */
    public function triggeredBy(string $trigger): bool
    {
        return $this->trigger === strtolower($trigger);
    }

    /**
     * Check if activation was triggered by Enter key
     */
    public function isEnterKey(): bool
    {
        return $this->triggeredBy('enter');
    }

    /**
     * Check if activation was triggered by Space key
     */
    public function isSpaceKey(): bool
    {
        return $this->triggeredBy('space');
    }

    /**
     * Get the component that was activated
     */
    public function getComponent(): Component
    {
        return $this->component;
    }

    /**
     * Get the trigger that caused activation
     */
    public function getTrigger(): string
    {
        return $this->trigger;
    }
}