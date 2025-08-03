<?php

declare(strict_types=1);

namespace Crumbls\Tui\Color;

final class FgBgColor
{
    public function __construct(public Color $fg, public Color $bg)
    {
    }

}
