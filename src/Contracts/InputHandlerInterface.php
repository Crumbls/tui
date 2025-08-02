<?php

declare(strict_types=1);

namespace Crumbls\Tui\Contracts;

/**
 * Handles parsing raw terminal input into meaningful events.
 */
interface InputHandlerInterface
{
    /**
     * Read and parse input from terminal, emitting appropriate events.
     * Returns true if input was processed, false if no input available.
     */
    public function processInput(float $timeout = 0): bool;

    /**
     * Parse a raw key sequence into a structured input event.
     */
    public function parseKeySequence(string $sequence): ?InputEventInterface;

    /**
     * Parse a mouse event sequence.
     */
    public function parseMouseEvent(string $sequence): ?InputEventInterface;

    /**
     * Enable/disable mouse input handling.
     */
    public function setMouseEnabled(bool $enabled): void;

    /**
     * Check if mouse input is enabled.
     */
    public function isMouseEnabled(): bool;
}