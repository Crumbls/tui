<?php

declare(strict_types=1);

namespace Crumbls\Tui\Exceptions;

use Exception;

/**
 * Exception for terminal-related errors.
 */
class TerminalException extends TuiException
{
    /**
     * Terminal failed to initialize or enter raw mode.
     */
    public static function initializationFailed(string $reason, ?Exception $previous = null): static
    {
        return static::fatal("Terminal initialization failed: {$reason}", 1001, $previous);
    }

    /**
     * Terminal size could not be determined.
     */
    public static function sizeDetectionFailed(?Exception $previous = null): static
    {
        return static::recoverable("Could not detect terminal size", 1002, $previous);
    }

    /**
     * Terminal read operation failed.
     */
    public static function readFailed(string $reason, ?Exception $previous = null): static
    {
        return static::recoverable("Terminal read failed: {$reason}", 1003, $previous);
    }

    /**
     * Terminal write operation failed.
     */
    public static function writeFailed(string $reason, ?Exception $previous = null): static
    {
        return static::recoverable("Terminal write failed: {$reason}", 1004, $previous);
    }

    /**
     * Raw mode could not be enabled/disabled.
     */
    public static function rawModeFailed(string $operation, ?Exception $previous = null): static
    {
        return static::fatal("Raw mode {$operation} failed", 1005, $previous);
    }

    /**
     * Mouse reporting could not be enabled/disabled.
     */
    public static function mouseReportingFailed(string $operation, ?Exception $previous = null): static
    {
        return static::recoverable("Mouse reporting {$operation} failed", 1006, $previous);
    }
}