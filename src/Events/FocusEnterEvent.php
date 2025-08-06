<?php

namespace Crumbls\Tui\Events;

use Crumbls\Tui\Components\Contracts\Component;

class FocusEnterEvent
{
    public function __construct(
        public readonly Component $component,
        public readonly ?Component $previousComponent = null
    ) {
    }

    /**
     * Check if focus came from a specific component
     */
    public function cameFrom(Component $component): bool
    {
        return $this->previousComponent && $this->previousComponent->getId() === $component->getId();
    }

    /**
     * Get the component that gained focus
     */
    public function getComponent(): Component
    {
        return $this->component;
    }

    /**
     * Get the component that previously had focus
     */
    public function getPreviousComponent(): ?Component
    {
        return $this->previousComponent;
    }
}