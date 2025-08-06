<?php

declare(strict_types=1);

namespace Crumbls\Tui\Contracts;

use Crumbls\Tui\Events\MouseEvent;

/**
 * Interface for components that can be selected/focused and receive user input.
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
     * Handle a key input event - returns true if handled, false to bubble up.
     */
    public function handleKeyInput(string $key): bool;

    /**
     * Handle a mouse input event - returns true if handled, false to bubble up.
     */
    public function handleMouseInput(MouseEvent $event): bool;

    /**
     * Get the selection priority (higher numbers get priority when overlapping).
     */
    public function getSelectionPriority(): int;

    /**
     * Set the selection priority.
     */
    public function setSelectionPriority(int $priority): static;

    /**
     * Get component unique identifier for focus management.
     */
    public function getId(): string;
}