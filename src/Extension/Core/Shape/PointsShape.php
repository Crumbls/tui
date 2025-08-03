<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Shape;

use Crumbls\Tui\Canvas\Shape;
use Crumbls\Tui\Color\Color;

/**
 * Render a set of points on the canvas.
 */
final class PointsShape implements Shape
{
    /**
     * @param array<int,array{float,float}> $coords
     */
    public function __construct(
        /**
         * Set of coordinates to draw, e.g. `[[0.0, 0.0], [2.0, 2.0], [4.0,4.0]]`
         */
        public array $coords,
        /**
         * Color of the points
         */
        public Color $color
    ) {
    }

    /**
     * @param list<array{float,float}> $coords
     */
    public static function new(array $coords, Color $color): self
    {
        return new self(
            $coords,
            $color
        );
    }
}
