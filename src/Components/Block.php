<?php

namespace Crumbls\Tui\Components;

class Block extends Component
{
    protected int $x = 0;
    protected int $y = 0;
    protected int $width = 20;
    protected int $height = 10;

    public static function make(string $title = ''): self
    {
        return (new self())->title($title);
    }

    public function position(int $x, int $y): self
    {
        $this->x = $x;
        $this->y = $y;
        return $this;
    }

    public function size(int $width, int $height): self
    {
        $this->width = $width;
        $this->height = $height;
        return $this;
    }
}