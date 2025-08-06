<?php

namespace Crumbls\Tui\Contracts;

use Crumbls\Tui\Components\Component;
use Crumbls\Tui\Events\MouseEvent;

interface FocusBusContract
{
    /**
     * Set focus to a specific component
     */
    public function setFocus(SelectableInterface $component): void;

    /**
     * Get the currently focused component
     */
    public function getFocused(): ?SelectableInterface;

    /**
     * Clear focus from all components
     */
    public function clearFocus(): void;

    /**
     * Focus the next selectable component
     */
    public function focusNext(): bool;

    /**
     * Focus the previous selectable component
     */
    public function focusPrevious(): bool;

    /**
     * Register a root component for focus management
     */
    public function registerRoot(Component $root): void;

    /**
     * Unregister a root component
     */
    public function unregisterRoot(Component $root): void;

    /**
     * Get the component at specified coordinates
     */
    public function getComponentAt(int $x, int $y): ?SelectableInterface;

    /**
     * Handle mouse click events for focus management
     */
    public function handleMouseClick(MouseEvent $event): bool;

    /**
     * Handle key input events for focused component
     */
    public function handleKeyInput(string $key): bool;

    /**
     * Get all selectable components
     */
    public function getSelectableComponents(): array;

    /**
     * Set the tab order for components
     */
    public function setTabOrder(array $componentIds): void;

    /**
     * Get the current tab order
     */
    public function getTabOrder(): array;

    /**
     * Enable or disable the focus bus
     */
    public function setEnabled(bool $enabled): void;

    /**
     * Check if focus bus is enabled
     */
    public function isEnabled(): bool;
}