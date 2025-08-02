<?php

declare(strict_types=1);

namespace Crumbls\Tui\Contracts;

use Crumbls\Tui\Terminal\Size;

/**
 * Terminal abstraction for all terminal operations.
 * This makes the entire system testable by allowing mock terminals.
 */
interface TerminalInterface
{
    /**
     * Get current terminal size.
     */
    public function getSize(): Size;

    /**
     * Read a key from terminal input with optional timeout.
     * Returns null if no input within timeout.
     */
    public function readKey(float $timeout = 0): ?string;

    /**
     * Write content to terminal output.
     */
    public function write(string $content): void;

    /**
     * Clear the terminal screen.
     */
    public function clear(): void;

    /**
     * Enable raw mode for key-by-key input.
     */
    public function enableRawMode(): void;

    /**
     * Disable raw mode and restore normal terminal.
     */
    public function disableRawMode(): void;

    /**
     * Check if terminal supports colors.
     */
    public function supportsColors(): bool;

    /**
     * Check if terminal supports mouse input.
     */
    public function supportsMouse(): bool;

    /**
     * Enable mouse reporting in the terminal.
     */
    public function enableMouseReporting(): void;

    /**
     * Disable mouse reporting in the terminal.
     */
    public function disableMouseReporting(): void;

    /**
     * Queue a command to be sent to terminal (PhpTui pattern).
     */
    public function queue(string $command): void;

    /**
     * Flush all queued commands to terminal.
     */
    public function flush(): void;

    /**
     * Move cursor to specific position with optimization.
     */
    public function moveCursor(int $x, int $y): void;

    /**
     * Get current cursor position from terminal.
     * Returns [x, y] or null if failed.
     */
    public function getCursorPosition(): ?array;

    /**
     * Set foreground color using RGB values.
     */
    public function setForegroundColor(int $r, int $g, int $b): void;

    /**
     * Set background color using RGB values.
     */
    public function setBackgroundColor(int $r, int $g, int $b): void;

    /**
     * Reset all colors and styles.
     */
    public function resetColors(): void;
}