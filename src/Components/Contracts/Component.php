<?php

declare(strict_types=1);

namespace Crumbls\Tui\Components\Contracts;

use Crumbls\Tui\Event\ClickEvent;
use Crumbls\Tui\Event\KeyPressEvent;

/**
 * Base interface for all TUI components.
 * 
 * Components are the building blocks of TUI applications, similar to DOM elements.
 * They can handle events, be focused, and contain other components.
 */
interface Component
{
    /**
     * Handle a key press event.
     * Return true if handled, false/null to bubble up.
     */
    public function handleKeyPress(KeyPressEvent $event): bool;

    /**
     * Handle a click event.
     * Return true if handled, false/null to bubble up.
     */
    public function handleClick(ClickEvent $event): bool;

    /**
     * Handle focus event.
     */
    public function handleFocus(): void;

    /**
     * Handle blur event.
     */
    public function handleBlur(): void;

    /**
     * Check if this component can receive focus.
     */
    public function canFocus(): bool;

    /**
     * Check if this component currently has focus.
     */
    public function hasFocus(): bool;

    /**
     * Give focus to this component.
     */
    public function focus(): void;

    /**
     * Remove focus from this component.
     */
    public function blur(): void;

    /**
     * Register a key press event handler.
     */
    public function onKeyPress(callable $handler): self;

    /**
     * Register a click event handler.
     */
    public function onClick(callable $handler): self;

    /**
     * Register a focus event handler.
     */
    public function onFocus(callable $handler): self;

    /**
     * Register a blur event handler.
     */
    public function onBlur(callable $handler): self;

    /**
     * Make this component focusable.
     */
    public function focusable(bool $focusable = true): self;

    /**
     * Get child components for event bubbling.
     * 
     * @return Component[]
     */
    public function getChildren(): array;

    /**
     * Get the parent component.
     */
    public function getParent(): ?Component;

    /**
     * Set the parent component.
     */
    public function setParent(?Component $parent): void;
}