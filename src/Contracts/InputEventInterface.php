<?php

declare(strict_types=1);

namespace Crumbls\Tui\Contracts;

/**
 * Represents a parsed input event from the terminal.
 */
interface InputEventInterface extends EventInterface
{
    /**
     * Get the input type (key, mouse, etc.).
     */
    public function getInputType(): string;

    /**
     * Get the raw input sequence that created this event.
     */
    public function getRawInput(): string;

    /**
     * Check if this input event should be handled by the application.
     * Some inputs might be system-level or ignored.
     */
    public function shouldHandle(): bool;
}