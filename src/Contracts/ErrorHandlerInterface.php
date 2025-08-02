<?php

declare(strict_types=1);

namespace Crumbls\Tui\Contracts;

use Crumbls\Tui\Exceptions\TuiException;
use Crumbls\Tui\Exceptions\TerminalException;
use Crumbls\Tui\Exceptions\RenderException;
use Exception;

/**
 * Handles errors gracefully, ensuring terminal cleanup and recovery.
 */
interface ErrorHandlerInterface
{
    /**
     * Handle terminal-specific errors.
     */
    public function handleTerminalError(TerminalException $e): void;

    /**
     * Handle rendering-specific errors.
     */
    public function handleRenderError(RenderException $e): void;

    /**
     * Handle general TUI errors.
     */
    public function handleTuiError(TuiException $e): void;

    /**
     * Handle unexpected exceptions.
     */
    public function handleUnexpectedError(Exception $e): void;

    /**
     * Determine if the application should continue after an error.
     */
    public function shouldContinue(Exception $e): bool;

    /**
     * Perform emergency cleanup (terminal restoration, etc.).
     */
    public function emergencyCleanup(): void;

    /**
     * Get error statistics and history.
     */
    public function getErrorStats(): array;

    /**
     * Register a cleanup handler to run on emergency shutdown.
     */
    public function registerCleanupHandler(callable $handler): void;
}