<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Shape;

use Crumbls\Tui\Canvas\Painter;
use Crumbls\Tui\Canvas\Shape;
use Crumbls\Tui\Canvas\ShapePainter;
use Crumbls\Tui\Position\FloatPosition;

final class PointsPainter implements ShapePainter
{
    public function draw(ShapePainter $shapePainter, Painter $painter, Shape $shape): void
    {
        if (!$shape instanceof PointsShape) {
            return;
        }

        foreach ($shape->coords as [$x, $y]) {
            if (!$point = $painter->getPoint(FloatPosition::at($x, $y))) {
                continue;
            }
            $painter->paint($point, $shape->color);
        }
    }
}
