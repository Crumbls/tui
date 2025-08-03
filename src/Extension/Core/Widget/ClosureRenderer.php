<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Closure;
use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Widget\Widget;
use Crumbls\Tui\Widget\WidgetRenderer;

final class ClosureRenderer implements WidgetRenderer
{
    /**
     * @param Closure(WidgetRenderer, Widget, Buffer, Area): void $renderer
     */
    public function __construct(private readonly Closure $renderer)
    {
    }

    public function render(WidgetRenderer $renderer, Widget $widget, Buffer $buffer, Area $area): void
    {
        ($this->renderer)($renderer, $widget, $buffer, $area);
    }
}
