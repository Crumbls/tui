<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget\Scrollbar;

use Crumbls\Tui\Symbol\BlockSet;
use Crumbls\Tui\Symbol\LineSet;

final class ScrollbarSymbols
{
    public function __construct(
        public string $track,
        public string $thumb,
        public string $begin,
        public string $end,
    ) {
    }

    public static function doubleVertical(): self
    {
        return new self(
            LineSet::DOUBLE_VERTICAL,
            BlockSet::FULL,
            '▲',
            '▼',
        );
    }

    public static function doubleHorizontal(): self
    {
        return new self(
            LineSet::DOUBLE_HORIZONTAL,
            BlockSet::FULL,
            begin: '◄',
            end: '►',
        );
    }

    public static function vertical(): self
    {
        return new self(
            LineSet::VERTICAL,
            BlockSet::FULL,
            begin: '↑',
            end: '↓',
        );
    }

    public static function horizontal(): self
    {
        return new self(
            LineSet::HORIZONTAL,
            BlockSet::FULL,
            begin: '←',
            end: '→',
        );
    }
}
