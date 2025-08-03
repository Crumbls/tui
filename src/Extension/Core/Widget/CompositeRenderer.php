<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Widget\Widget;
use Crumbls\Tui\Widget\WidgetRenderer;

final class CompositeRenderer implements WidgetRenderer
{
    public function render(
        WidgetRenderer $renderer,
        Widget $widget,
        Buffer $buffer,
        Area $area,
    ): void {
        if (!$widget instanceof CompositeWidget) {
            return;
        }

        array_map(static function (Widget $widget) use ($renderer, $buffer, $area): void {
            $renderer->render($renderer, $widget, $buffer, $area);
        }, $widget->widgets);
    }
}
