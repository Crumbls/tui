<?php

declare(strict_types=1);

namespace Crumbls\Tui\Components\Concerns;

use Crumbls\Tui\Events\MouseEvent;

/**
 * Trait for components that can be selected/focused.
 */
trait Selectable
{
    protected bool $selectable = false;
    protected bool $selected = false;
    protected bool $canReceiveKeyboard = true;
    protected bool $canReceiveMouse = true;
    protected int $selectionPriority = 0;

    /**
     * Make component selectable
     */
    public function selectable(bool $selectable = true): self
    {
        $this->selectable = $selectable;
        return $this;
    }

    /**
     * Check if this component can be selected/focused
     */
    public function isSelectable(): bool
    {
        return $this->selectable;
    }

    /**
     * Check if this component is currently selected/focused
     */
    public function isSelected(): bool
    {
        return $this->selected;
    }

    /**
     * Set the selection state of this component
     */
    public function setSelected(bool $selected): static
    {
        $this->selected = $selected;
        return $this;
    }

    /**
     * Check if this component can receive keyboard input
     */
    public function canReceiveKeyboard(): bool
    {
        return $this->canReceiveKeyboard && $this->isSelectable();
    }

    /**
     * Check if this component can receive mouse input
     */
    public function canReceiveMouse(): bool
    {
        return $this->canReceiveMouse && $this->isSelectable();
    }

    /**
     * Set keyboard input capability
     */
    public function keyboardInput(bool $enabled = true): self
    {
        $this->canReceiveKeyboard = $enabled;
        return $this;
    }

    /**
     * Set mouse input capability
     */
    public function mouseInput(bool $enabled = true): self
    {
        $this->canReceiveMouse = $enabled;
        return $this;
    }

    /**
     * Handle a key input event - returns true if handled, false to bubble up
     */
    public function handleKeyInput(string $key): bool
    {
        // Default implementation - can be overridden by components
        return false;
    }

    /**
     * Handle a mouse input event - returns true if handled, false to bubble up
     */
    public function handleMouseInput(MouseEvent $event): bool
    {
        // Default implementation - can be overridden by components
        return false;
    }

    /**
     * Get the selection priority (higher numbers get priority when overlapping)
     */
    public function getSelectionPriority(): int
    {
        return $this->selectionPriority;
    }

    /**
     * Set the selection priority
     */
    public function setSelectionPriority(int $priority): static
    {
        $this->selectionPriority = $priority;
        return $this;
    }

    /**
     * Set selection priority (fluent method)
     */
    public function priority(int $priority): self
    {
        return $this->setSelectionPriority($priority);
    }
}