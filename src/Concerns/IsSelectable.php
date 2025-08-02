<?php

declare(strict_types=1);

namespace Crumbls\Tui\Concerns;

use Crumbls\Tui\Contracts\InputEventInterface;

/**
 * Trait providing selection and input handling for components.
 */
trait IsSelectable
{
    protected bool $selectable = true;
    protected bool $selected = false;
    protected bool $canReceiveKeyboard = true;
    protected bool $canReceiveMouse = true;
    protected int $selectionPriority = 0;

    public function isSelectable(): bool
    {
        return $this->selectable;
    }

    public function setSelectable(bool $selectable): static
    {
        $this->selectable = $selectable;
        if (!$selectable) {
            $this->selected = false;
        }
        return $this;
    }

    public function isSelected(): bool
    {
        return $this->selected && $this->selectable;
    }

    public function setSelected(bool $selected): static
    {
        if ($this->selectable) {
            $this->selected = $selected;
        }
        return $this;
    }

    public function canReceiveKeyboard(): bool
    {
        return $this->canReceiveKeyboard && $this->isSelectable() && $this->isSelected();
    }

    public function canReceiveMouse(): bool
    {
        return $this->canReceiveMouse && $this->isSelectable();
    }

    public function setCanReceiveKeyboard(bool $canReceive): static
    {
        $this->canReceiveKeyboard = $canReceive;
        return $this;
    }

    public function setCanReceiveMouse(bool $canReceive): static
    {
        $this->canReceiveMouse = $canReceive;
        return $this;
    }

    public function handleKeyInput(InputEventInterface $event): bool
    {
        // Default implementation - override in specific components
        return false;
    }

    public function handleMouseInput(InputEventInterface $event): bool
    {
        // Default implementation - override in specific components
        return false;
    }

    public function getSelectionPriority(): int
    {
        return $this->selectionPriority;
    }

    public function setSelectionPriority(int $priority): static
    {
        $this->selectionPriority = $priority;
        return $this;
    }

    /**
     * Helper method to focus this component (select it and deselect others).
     */
    public function focus(): static
    {
        return $this->setSelected(true);
    }

    /**
     * Helper method to blur this component (deselect it).
     */
    public function blur(): static
    {
        return $this->setSelected(false);
    }

    /**
     * Helper method to toggle selection state.
     */
    public function toggleSelection(): static
    {
        return $this->setSelected(!$this->selected);
    }
}