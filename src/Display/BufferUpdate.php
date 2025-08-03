<?php

declare(strict_types=1);

namespace Crumbls\Tui\Display;

use Crumbls\Tui\Position\Position;

final class BufferUpdate
{
    public function __construct(public Position $position, public Cell $cell)
    {
    }
}
