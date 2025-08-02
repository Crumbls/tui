<?php

declare(strict_types=1);

namespace Crumbls\Tui\Contracts;

/**
 * Interface for components that have position and dimensions in 2D space.
 */
interface PositionableInterface
{
    /**
     * Get the X coordinate (left edge).
     */
    public function getX(): int;

    /**
     * Get the Y coordinate (top edge).
     */
    public function getY(): int;

    /**
     * Get the width of the component.
     */
    public function getWidth(): int;

    /**
     * Get the height of the component.
     */
    public function getHeight(): int;

    /**
     * Get the right edge X coordinate (x + width - 1).
     */
    public function getX2(): int;

    /**
     * Get the bottom edge Y coordinate (y + height - 1).
     */
    public function getY2(): int;

    /**
     * Set the position of the component.
     */
    public function setPosition(int $x, int $y): static;

    /**
     * Set the size of the component.
     */
    public function setSize(int $width, int $height): static;

    /**
     * Set both position and size at once.
     */
    public function setBounds(int $x, int $y, int $width, int $height): static;

    /**
     * Check if a point is within this component's bounds.
     */
    public function containsPoint(int $x, int $y): bool;

    /**
     * Check if this component overlaps with another positionable component.
     */
    public function overlaps(PositionableInterface $other): bool;

    /**
     * Get the absolute position relative to the root component.
     * This accounts for parent component positioning.
     */
    public function getAbsoluteX(): int;

    /**
     * Get the absolute Y position relative to the root component.
     */
    public function getAbsoluteY(): int;

    /**
     * Get the parent component (for nested positioning).
     */
    public function getParent(): ?PositionableInterface;

    /**
     * Set the parent component.
     */
    public function setParent(?PositionableInterface $parent): static;
}