<?php

declare(strict_types=1);

namespace Crumbls\Tui\Terminal\Events;

/**
 * Represents a key press event
 */
class KeyEvent extends Event
{
    public function __construct(
        public readonly string $key,
        public readonly bool $ctrl = false,
        public readonly bool $alt = false,
        public readonly bool $shift = false
    ) {
    }
}