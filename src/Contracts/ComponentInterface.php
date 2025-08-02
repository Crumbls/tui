<?php

declare(strict_types=1);

namespace Crumbls\Tui\Contracts;

/**
 * Core interface for all TUI components.
 */
interface ComponentInterface extends PositionableInterface, SelectableInterface
{
    /**
     * Render the component to a string representation.
     */
    public function render(): string;

    /**
     * Get the unique component identifier.
     */
    public function getId(): string;

    /**
     * Set the component identifier.
     */
    public function setId(string $id): static;

    /**
     * Check if the component is visible.
     */
    public function isVisible(): bool;

    /**
     * Set the visibility of the component.
     */
    public function setVisible(bool $visible): static;

    /**
     * Add a child component.
     */
    public function addChild(ComponentInterface $child): static;

    /**
     * Remove a child component by ID.
     */
    public function removeChild(string $childId): static;

    /**
     * Get all child components.
     */
    public function getChildren(): array;

    /**
     * Get a specific child component by ID.
     */
    public function getChild(string $childId): ?ComponentInterface;

    /**
     * Find the topmost component at a given point.
     */
    public function getComponentAt(int $x, int $y): ?ComponentInterface;

    /**
     * Get all selectable components in this component tree.
     */
    public function getSelectableComponents(): array;

    /**
     * Set an attribute value.
     */
    public function setAttribute(string $key, mixed $value): static;

    /**
     * Get an attribute value.
     */
    public function getAttribute(string $key, mixed $default = null): mixed;

    /**
     * Check if an attribute exists.
     */
    public function hasAttribute(string $key): bool;

    /**
     * Get debug information about this component.
     */
    public function getDebugInfo(): array;
}