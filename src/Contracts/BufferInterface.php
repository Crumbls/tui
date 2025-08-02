<?php

declare(strict_types=1);

namespace Crumbls\Tui\Contracts;

/**
 * Interface for managing screen buffer operations with coordinate tracking.
 */
interface BufferInterface
{
    /**
     * Write content at a specific position in the buffer.
     */
    public function write(int $x, int $y, string $content): void;

    /**
     * Write content within a specific rectangular region.
     */
    public function writeInRegion(int $x, int $y, int $width, int $height, string $content): void;

    /**
     * Clear the entire buffer.
     */
    public function clear(): void;

    /**
     * Clear a specific rectangular region.
     */
    public function clearRegion(int $x, int $y, int $width, int $height): void;

    /**
     * Get the content at a specific position.
     */
    public function getAt(int $x, int $y): string;

    /**
     * Get content from a rectangular region.
     */
    public function getRegion(int $x, int $y, int $width, int $height): array;

    /**
     * Get the buffer dimensions.
     */
    public function getWidth(): int;
    public function getHeight(): int;

    /**
     * Resize the buffer.
     */
    public function resize(int $width, int $height): void;

    /**
     * Compare this buffer with another and return differences.
     * Returns array of changes: [['x' => int, 'y' => int, 'old' => string, 'new' => string], ...]
     */
    public function diff(BufferInterface $other): array;

    /**
     * Mark a region as dirty (needs re-rendering).
     */
    public function markDirty(int $x, int $y, int $width, int $height): void;

    /**
     * Check if a region is dirty.
     */
    public function isDirty(int $x, int $y, int $width, int $height): bool;

    /**
     * Get all dirty regions.
     */
    public function getDirtyRegions(): array;

    /**
     * Clear all dirty regions.
     */
    public function clearDirtyRegions(): void;

    /**
     * Copy content from another buffer.
     */
    public function copyFrom(BufferInterface $source): void;

    /**
     * Copy a region from another buffer.
     */
    public function copyRegionFrom(
        BufferInterface $source, 
        int $sourceX, int $sourceY, int $width, int $height,
        int $destX, int $destY
    ): void;

    /**
     * Fill a region with a character or pattern.
     */
    public function fill(int $x, int $y, int $width, int $height, string $char = ' '): void;

    /**
     * Get the entire buffer as a string representation.
     */
    public function toString(): string;

    /**
     * Check if coordinates are within buffer bounds.
     */
    public function isValidPosition(int $x, int $y): bool;

    /**
     * Clip coordinates to buffer bounds.
     */
    public function clipToBounds(int $x, int $y, int $width, int $height): array;
}