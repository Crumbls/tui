<?php

declare(strict_types=1);

namespace Crumbls\Tui\Canvas;

use Crumbls\Tui\Position\FloatPosition;
use Crumbls\Tui\Text\Line;

final class Label
{
    public function __construct(public FloatPosition $position, public Line $line)
    {
    }

    public function width(): int
    {
        return $this->line->width();
    }
}
