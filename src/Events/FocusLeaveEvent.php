<?php

namespace Crumbls\Tui\Events;

use Crumbls\Tui\Components\Contracts\Component;

class FocusLeaveEvent
{
    public function __construct(
        public readonly Component $component,
        public readonly ?Component $nextComponent = null
    ) {
    }

    /**
     * Check if focus is going to a specific component
     */
    public function goingTo(Component $component): bool
    {
        return $this->nextComponent && $this->nextComponent->getId() === $component->getId();
    }

    /**
     * Get the component that lost focus
     */
    public function getComponent(): Component
    {
        return $this->component;
    }

    /**
     * Get the component that will receive focus
     */
    public function getNextComponent(): ?Component
    {
        return $this->nextComponent;
    }
}