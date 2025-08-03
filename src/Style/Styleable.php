<?php

declare(strict_types=1);

namespace Crumbls\Tui\Style;

interface Styleable
{
    public function patchStyle(Style $style): self;
}
