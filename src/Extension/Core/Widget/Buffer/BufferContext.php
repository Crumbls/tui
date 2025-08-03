<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget\Buffer;

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Widget\Widget;
use Crumbls\Tui\Widget\WidgetRenderer;

final class BufferContext
{
    public function __construct(private readonly WidgetRenderer $renderer, public readonly Buffer $buffer, public readonly Area $area)
    {
    }

    public function draw(Widget $widget, ?Area $area = null): void
    {
        $this->renderer->render($this->renderer, $widget, $this->buffer, $area ?? $this->area);
    }
}
