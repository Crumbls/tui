<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Shape;

use Crumbls\Tui\Canvas\Shape;
use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\Color\Color;
use Crumbls\Tui\Position\FloatPosition;

/**
 * Draws a circle at with the specified radius and color
 */
final class CircleShape implements Shape
{
    public function __construct(
        /**
         * Position of the circle
         */
        public FloatPosition $position,
        /**
         * Radius of the circle
         */
        public float $radius,
        /**
         * Color of the circle
         */
        public Color $color,
    ) {
    }

    public static function fromScalars(float $x, float $y, float $radius): self
    {
        return new self(FloatPosition::at($x, $y), $radius, AnsiColor::Reset);
    }

    public function color(Color $color): self
    {
        $this->color = $color;

        return $this;
    }
}
