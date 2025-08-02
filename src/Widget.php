<?php

declare(strict_types=1);

namespace Crumbls\Tui;

/**
 * Minimal base widget class.
 */
abstract class Widget
{
    public static function make(): static
    {
        return new static();
    }

    abstract public function render(): string;
}