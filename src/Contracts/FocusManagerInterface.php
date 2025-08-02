<?php

declare(strict_types=1);

namespace Crumbls\Tui\Contracts;

/**
 * Interface for managing focus/selection across components.
 */
interface FocusManagerInterface
{
    /**
     * Set focus to a specific component.
     */
    public function setFocus(SelectableInterface $component): void;

    /**
     * Get the currently focused component.
     */
    public function getFocused(): ?SelectableInterface;

    /**
     * Clear focus from all components.
     */
    public function clearFocus(): void;

    /**
     * Move focus to the next selectable component.
     */
    public function focusNext(): bool;

    /**
     * Move focus to the previous selectable component.
     */
    public function focusPrevious(): bool;

    /**
     * Register a component tree for focus management.
     */
    public function registerRoot(PositionableInterface $root): void;

    /**
     * Unregister a component tree from focus management.
     */
    public function unregisterRoot(PositionableInterface $root): void;

    /**
     * Find the component at a specific screen coordinate.
     */
    public function getComponentAt(int $x, int $y): ?SelectableInterface;

    /**
     * Handle a mouse click by focusing the component at that position.
     */
    public function handleMouseClick(int $x, int $y): bool;

    /**
     * Get all registered selectable components.
     */
    public function getSelectableComponents(): array;

    /**
     * Set the focus order for tab navigation.
     */
    public function setTabOrder(array $componentIds): void;

    /**
     * Get the current tab order.
     */
    public function getTabOrder(): array;

    /**
     * Enable or disable focus management.
     */
    public function setEnabled(bool $enabled): void;

    /**
     * Check if focus management is enabled.
     */
    public function isEnabled(): bool;
}