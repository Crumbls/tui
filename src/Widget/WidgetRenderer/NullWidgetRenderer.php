<?php

declare(strict_types=1);

namespace Crumbls\Tui\Widget\WidgetRenderer;

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Widget\Widget;
use Crumbls\Tui\Widget\WidgetRenderer;

/**
 * This renderer does nothing.
 *
 * It should typically be used as the "renderer" when
 * calling the aggregate renderer to satisfy the contract.
 */
final class NullWidgetRenderer implements WidgetRenderer
{
    public function render(WidgetRenderer $renderer, Widget $widget, Buffer $buffer, Area $area): void
    {
    }
}
