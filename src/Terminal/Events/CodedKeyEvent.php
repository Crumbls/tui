<?php

declare(strict_types=1);

namespace Crumbls\Tui\Terminal\Events;

use Crumbls\Tui\Terminal\KeyCode;
use Crumbls\Tui\Terminal\KeyModifiers;

/**
 * Coded key event - matches PhpTui's CodedKeyEvent interface
 */
class CodedKeyEvent
{
    public function __construct(
        public readonly KeyCode $code,
        public readonly KeyModifiers $modifiers = KeyModifiers::NONE
    ) {
    }
}