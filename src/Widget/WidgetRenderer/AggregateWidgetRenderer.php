<?php

declare(strict_types=1);

namespace Crumbls\Tui\Widget\WidgetRenderer;

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Widget\Widget;
use Crumbls\Tui\Widget\WidgetRenderer;

/**
 * Will iterate over all widget renderers to render the widget Each renderer
 * should return immediately if the widget is not of the correct type.
 *
 * This renderer will always pass _itself_ as the renderer to the passed in widgets
 * and so the `$renderer` parameter is unused.
 */
final class AggregateWidgetRenderer implements WidgetRenderer
{
    /**
     * @param WidgetRenderer[] $renderers
     */
    public function __construct(private readonly array $renderers)
    {
    }

    public function render(WidgetRenderer $renderer, Widget $widget, Buffer $buffer, Area $area): void
    {
        if ($widget instanceof WidgetRenderer) {
            $widget->render($this, $widget, $buffer, $buffer->area());

            return;
        }

        foreach ($this->renderers as $aggregateRenderer) {
            $aggregateRenderer->render($this, $widget, $buffer, $area);
        }
    }
}
