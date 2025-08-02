<?php

declare(strict_types=1);

namespace Crumbls\Tui\Exceptions;

use Exception;

/**
 * Exception for rendering-related errors.
 */
class RenderException extends TuiException
{
    /**
     * Buffer operations failed.
     */
    public static function bufferFailed(string $operation, ?Exception $previous = null): static
    {
        return static::recoverable("Buffer {$operation} failed", 2001, $previous);
    }

    /**
     * Widget rendering failed.
     */
    public static function widgetRenderFailed(string $widgetType, ?Exception $previous = null): static
    {
        return static::recoverable("Widget '{$widgetType}' render failed", 2002, $previous);
    }

    /**
     * Screen clear or update failed.
     */
    public static function screenUpdateFailed(?Exception $previous = null): static
    {
        return static::recoverable("Screen update failed", 2003, $previous);
    }

    /**
     * Invalid render dimensions.
     */
    public static function invalidDimensions(int $width, int $height): static
    {
        return static::recoverable("Invalid render dimensions: {$width}x{$height}");
    }
}