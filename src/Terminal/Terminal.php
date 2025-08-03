<?php

declare(strict_types=1);

namespace Crumbls\Tui\Terminal;

use PhpTui\Term\Terminal as PhpTuiTerminal;

/**
 * A fluent terminal interface that provides Laravel-style method chaining
 * and simplified terminal operations.
 * 
 * Uses our native terminal for control, but PhpTui's input handling.
 */
class Terminal
{
    private readonly PhpTuiTerminal $inputTerminal;

    public function __construct(
        private readonly NativeTerminal $terminal
    ) {
        // Use PhpTui terminal ONLY for input handling
        $this->inputTerminal = PhpTuiTerminal::new();
    }

    public static function new(): self
    {
        return new self(NativeTerminal::new());
    }

    /**
     * Show or hide the cursor
     */
    public function showCursor(bool $show = true): self
    {
        $this->terminal->showCursor($show);
        return $this;
    }

    /**
     * Hide the cursor (convenience method)
     */
    public function hideCursor(): self
    {
        $this->terminal->hideCursor();
        return $this;
    }

    /**
     * Enable or disable alternate screen
     */
    public function alternateScreen(bool $enable = true): self
    {
        $this->terminal->alternateScreen($enable);
        return $this;
    }

    /**
     * Clear the terminal screen
     */
    public function clear(): self
    {
        $this->terminal->clear();
        return $this;
    }

    /**
     * Enable or disable mouse capture
     */
    public function mouseCapture(bool $enable = true): self
    {
        $this->terminal->mouseCapture($enable);
        return $this;
    }

    /**
     * Move cursor to specific position
     */
    public function moveCursor(int $x, int $y): self
    {
        $this->terminal->moveCursor($x, $y);
        return $this;
    }

    /**
     * Move cursor by relative amount
     */
    public function moveCursorBy(int $dx = 0, int $dy = 0): self
    {
        $this->terminal->moveCursorBy($dx, $dy);
        return $this;
    }

    /**
     * Set terminal title
     */
    public function title(string $title): self
    {
        $this->terminal->title($title);
        return $this;
    }

    /**
     * Write text to terminal
     */
    public function write(string $text): self
    {
        $this->terminal->write($text);
        return $this;
    }

    /**
     * Print text to terminal
     */
    public function print(string $text): self
    {
        $this->terminal->print($text);
        return $this;
    }

    /**
     * Enable raw mode
     */
    public function rawMode(bool $enable = true): self
    {
        $this->terminal->rawMode($enable);
        return $this;
    }

    /**
     * Flush output to the terminal
     */
    public function flush(): self
    {
        $this->terminal->flush();
        return $this;
    }

    /**
     * Get terminal size information
     */
    public function size(): ?array
    {
        return $this->terminal->size();
    }

    /**
     * Read input from terminal
     */
    public function readInput(float $timeout = 0.1): ?string
    {
        return $this->terminal->readInput($timeout);
    }

    /**
     * Read a single character
     */
    public function readChar(): ?string
    {
        return $this->terminal->readChar();
    }

    /**
     * Get the underlying native terminal instance
     */
    public function underlying(): NativeTerminal
    {
        return $this->terminal;
    }

    /**
     * Set up terminal for TUI application (common setup)
     */
    public function setupForTui(): self
    {
        $this->terminal->setupForTui();
        
        // Also enable raw mode on PhpTui terminal for input handling
        try {
            $this->inputTerminal->enableRawMode();
        } catch (\Throwable $e) {
            // Continue if raw mode fails
        }
        
        return $this;
    }

    /**
     * Get the event provider for reading terminal events
     * Uses PhpTui's proven input handling
     */
    public function events()
    {
        return $this->inputTerminal->events();
    }

    /**
     * Automatic cleanup when the terminal object is destroyed
     */
    public function __destruct()
    {
        try {
            // Cleanup input terminal
            $this->inputTerminal->disableRawMode();
        } catch (\Throwable $e) {
            // Ignore cleanup errors
        }
        
        try {
            $this->rawMode(false)
                ->mouseCapture(false)
                ->alternateScreen(false)
                ->showCursor()
                ->clear()
                ->flush();
        } catch (\Throwable $e) {
            // Ignore cleanup errors during destruction
        }
    }
}