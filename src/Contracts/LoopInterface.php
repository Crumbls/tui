<?php

declare(strict_types=1);

namespace Crumbls\Tui\Contracts;

/**
 * Main event loop interface for the TUI framework.
 * Fluent, chainable API with sensible defaults.
 * All terminal/session management handled internally.
 */
interface LoopInterface
{
    /**
     * Set the callback to run every tick.
     */
    public function onTick(callable $callback): static;

    /**
     * Set the callback to run when a redraw is needed.
     */
    public function onRender(callable $callback): static;

    /**
     * Set the callback to run when input is received.
     */
    public function onInput(callable $callback): static;

    /**
     * Set the tick rate (in Hz).
     */
    public function setTickRate(int $hz): static;

    /**
     * Start the main loop (blocking until stopped).
     */
    public function start(): void;

    /**
     * Stop the loop gracefully.
     */
    public function stop(): void;

    /**
     * Run a single tick (for testing or advanced use).
     * Returns false if the loop should exit.
     */
    public function tick(): bool;
}
