<?php

declare(strict_types=1);

namespace Crumbls\Tui\Exceptions;

use Exception;

/**
 * Exception for input handling errors.
 */
class InputException extends TuiException
{
    /**
     * Input parsing failed.
     */
    public static function parseFailed(string $sequence, ?Exception $previous = null): static
    {
        $hex = bin2hex($sequence);
        return static::recoverable("Failed to parse input sequence: {$hex}", 3001, $previous);
    }

    /**
     * Input handler initialization failed.
     */
    public static function handlerInitFailed(string $reason, ?Exception $previous = null): static
    {
        return static::fatal("Input handler initialization failed: {$reason}", 3002, $previous);
    }

    /**
     * Input processing timeout.
     */
    public static function processingTimeout(float $timeout): static
    {
        return static::recoverable("Input processing timeout after {$timeout}s", 3003);
    }
}