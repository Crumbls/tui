<?php

declare(strict_types=1);

namespace Crumbls\Tui\Support;

class Borders
{
    public const NONE = 0;
    public const TOP = 1;
    public const RIGHT = 2;
    public const BOTTOM = 4;
    public const LEFT = 8;
    public const ALL = self::TOP | self::RIGHT | self::BOTTOM | self::LEFT;
}