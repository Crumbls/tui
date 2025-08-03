<?php

declare(strict_types=1);

namespace Crumbls\Tui\Terminal;

/**
 * Key modifiers - matches PhpTui's KeyModifiers
 */
enum KeyModifiers: int
{
    case NONE = 0;
    case SHIFT = 1;
    case CONTROL = 2;
    case ALT = 4;
    case META = 8;
}