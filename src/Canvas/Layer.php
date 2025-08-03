<?php

declare(strict_types=1);

namespace Crumbls\Tui\Canvas;

use Crumbls\Tui\Color\FgBgColor;

final class Layer
{
    /**
     * @param FgBgColor[] $colors
     * @param string[] $chars
     */
    public function __construct(public array $chars, public array $colors)
    {
    }

}
