<?php

declare(strict_types=1);

namespace Crumbls\Tui\Terminal\Events;

use Crumbls\Tui\Terminal\KeyModifiers;

/**
 * Character key event - matches PhpTui's CharKeyEvent interface
 */
class CharKeyEvent
{
    public function __construct(
        public readonly string $char,
        public readonly KeyModifiers $modifiers = KeyModifiers::NONE
    ) {
    }
}