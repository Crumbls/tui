<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Layout\Layout;
use Crumbls\Tui\Widget\Widget;
use Crumbls\Tui\Widget\WidgetRenderer;
use RuntimeException;

final class GridRenderer implements WidgetRenderer
{
    public function render(WidgetRenderer $renderer, Widget $widget, Buffer $buffer, Area $area): void
    {
        if (!$widget instanceof GridWidget) {
            return;
        }

        $layout = Layout::default()
            ->constraints($widget->constraints)
            ->direction($widget->direction)
            ->split($area);

        foreach ($widget->widgets as $index => $gridWidget) {
            if (!$layout->has($index)) {
                throw new RuntimeException(sprintf(
                    'Widget at offset %d has no corresponding constraint. ' .
                    'Ensure that the number of constraints match or exceed the number of widgets',
                    $index
                ));
            }
            $cellArea = $layout->get($index);
            $subBuffer = Buffer::empty($cellArea);
            $renderer->render($renderer, $gridWidget, $subBuffer, $subBuffer->area());
            $buffer->putBuffer($cellArea->position, $subBuffer);
        }
    }
}
