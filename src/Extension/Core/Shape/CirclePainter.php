<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Shape;

use Crumbls\Tui\Canvas\Painter;
use Crumbls\Tui\Canvas\Shape;
use Crumbls\Tui\Canvas\ShapePainter;
use Crumbls\Tui\Position\FloatPosition;

final class CirclePainter implements ShapePainter
{
    public function draw(ShapePainter $shapePainter, Painter $painter, Shape $shape): void
    {
        if (!$shape instanceof CircleShape) {
            return;
        }

        foreach (range(0, 360) as $degree) {
            $radians = deg2rad($degree);
            $circleX = $shape->radius * cos($radians) + $shape->position->x;
            $circleY = $shape->radius * sin($radians) + $shape->position->y;
            if ($point = $painter->getPoint(FloatPosition::at($circleX, $circleY))) {
                $painter->paint($point, $shape->color);
            }
        }
    }
}
