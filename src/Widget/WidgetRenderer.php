<?php

declare(strict_types=1);

namespace Crumbls\Tui\Widget;

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;

interface WidgetRenderer
{
    public function render(WidgetRenderer $renderer, Widget $widget, Buffer $buffer, Area $area): void;
}
