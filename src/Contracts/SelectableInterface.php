<?php

declare(strict_types=1);

namespace Crumbls\Tui\Contracts;

/**
 * Interface for components that can be selected/focused and interact with user input.
 */
interface SelectableInterface
{
    /**
     * Check if this component can be selected/focused.
     */
    public function isSelectable(): bool;

    /**
     * Check if this component is currently selected/focused.
     */
    public function isSelected(): bool;

    /**
     * Set the selection state of this component.
     */
    public function setSelected(bool $selected): static;

    /**
     * Check if this component can receive keyboard input.
     */
    public function canReceiveKeyboard(): bool;

    /**
     * Check if this component can receive mouse input.
     */
    public function canReceiveMouse(): bool;

    /**
     * Handle a key input event.
     */
    public function handleKeyInput(InputEventInterface $event): bool;

    /**
     * Handle a mouse input event.
     */
    public function handleMouseInput(InputEventInterface $event): bool;

    /**
     * Get the selection priority (higher numbers get priority when overlapping).
     */
    public function getSelectionPriority(): int;

    /**
     * Set the selection priority.
     */
    public function setSelectionPriority(int $priority): static;
}