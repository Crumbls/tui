<?php

namespace Crumbls\Tui\Layout;

use Crumbls\Tui\Components\Contracts\Component;

abstract class Layout
{
    protected int $x = 0;
    protected int $y = 0;
    protected int $width = 0;
    protected int $height = 0;

    public function __construct(int $x, int $y, int $width, int $height)
    {
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Calculate positions for all components
     * Returns array of [component => ['x' => int, 'y' => int, 'width' => int, 'height' => int]]
     */
    abstract public function calculate(array $components): array;

    public function getX(): int { return $this->x; }
    public function getY(): int { return $this->y; }
    public function getWidth(): int { return $this->width; }
    public function getHeight(): int { return $this->height; }
}