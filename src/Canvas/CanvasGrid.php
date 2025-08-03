<?php

declare(strict_types=1);

namespace Crumbls\Tui\Canvas;

use Crumbls\Tui\Color\Color;
use Crumbls\Tui\Position\Position;

abstract class CanvasGrid
{
    abstract public function resolution(): Resolution;

    abstract public function save(): Layer;

    abstract public function reset(): void;

    abstract public function paint(Position $position, Color $color): void;
}
