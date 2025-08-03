<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Shape;

use Crumbls\Tui\Canvas\Painter;
use Crumbls\Tui\Canvas\Shape;
use Crumbls\Tui\Canvas\ShapePainter;

final class NullShapePainter implements ShapePainter
{
    public function draw(ShapePainter $shapePainter, Painter $painter, Shape $shape): void
    {
    }
}
