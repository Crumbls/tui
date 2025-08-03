<?php

declare(strict_types=1);

namespace Crumbls\Tui\Canvas;

final class Resolution
{
    public function __construct(public int $width, public int $height)
    {
    }
}
