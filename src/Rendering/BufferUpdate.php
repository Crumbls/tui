<?php

declare(strict_types=1);

namespace Crumbls\Tui\Rendering;

/**
 * Represents a single buffer cell update.
 */
class BufferUpdate
{
    public function __construct(
        public int $x,
        public int $y,
        public Cell $cell
    ) {
    }

    public function getPosition(): array
    {
        return [$this->x, $this->y];
    }
}