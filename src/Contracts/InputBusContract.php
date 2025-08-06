<?php

namespace Crumbls\Tui\Contracts;

interface InputBusContract
{
    /**
     * Start listening for input events
     */
    public function startListening(): void;

    /**
     * Stop listening for input events
     */
    public function stopListening(): void;

    /**
     * Check if currently listening for input
     */
    public function isListening(): bool;

    /**
     * Read a single keystroke (blocking)
     */
    public function readKey(): ?string;

    /**
     * Check if input is available (non-blocking)
     */
    public function hasInput(): bool;

    /**
     * Register a keystroke handler
     */
    public function onKeyPress(callable $handler): void;

    /**
     * Register a mouse event handler
     */
    public function onMouseEvent(callable $handler): void;

    /**
     * Enable or disable mouse tracking in terminal
     */
    public function hasMouseTracking(bool $enabled = true): void;

    /**
     * Register a component's position for hit testing
     */
    public function registerComponent($component, int $x, int $y, int $width, int $height, int $zIndex = 0): void;

    /**
     * Get the component at given coordinates
     */
    public function getComponentAt(int $x, int $y);

    /**
     * Clear all registered components
     */
    public function clearComponents(): void;
}