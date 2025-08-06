<?php

namespace Crumbls\Tui\Contracts;

interface TerminalContract
{
    /**
     * Get terminal width
     */
    public function getWidth(): int;

    /**
     * Get terminal height
     */
    public function getHeight(): int;

    /**
     * Get terminal dimensions as [width, height]
     */
    public function getDimensions(): array;

    /**
     * Enter raw mode for input handling
     */
    public function enterRawMode(): self;

    /**
     * Exit raw mode
     */
    public function exitRawMode(): self;

    /**
     * Check if terminal is in raw mode
     */
    public function isRawMode(): bool;

    /**
     * Clear the terminal screen
     */
    public function clear(): self;

    /**
     * Move cursor to specified position
     */
    public function moveCursor(int $x, int $y): self;

    /**
     * Hide the cursor
     */
    public function hideCursor(): self;

    /**
     * Show the cursor
     */
    public function showCursor(): self;

    /**
     * Enable alternate screen buffer
     */
    public function enableAlternateScreen(): self;

    /**
     * Disable alternate screen buffer
     */
    public function disableAlternateScreen(): self;
}