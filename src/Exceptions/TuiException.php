<?php

declare(strict_types=1);

namespace Crumbls\Tui\Exceptions;

use Exception;

/**
 * Base exception for all TUI-related errors.
 */
class TuiException extends Exception
{
    protected bool $recoverable = true;

    public function __construct(string $message, int $code = 0, ?Exception $previous = null, bool $recoverable = true)
    {
        parent::__construct($message, $code, $previous);
        $this->recoverable = $recoverable;
    }

    /**
     * Whether this error allows the application to continue running.
     */
    public function isRecoverable(): bool
    {
        return $this->recoverable;
    }

    /**
     * Create a fatal (non-recoverable) exception.
     */
    public static function fatal(string $message, int $code = 0, ?Exception $previous = null): static
    {
        return new static($message, $code, $previous, false);
    }

    /**
     * Create a recoverable exception.
     */
    public static function recoverable(string $message, int $code = 0, ?Exception $previous = null): static
    {
        return new static($message, $code, $previous, true);
    }
}