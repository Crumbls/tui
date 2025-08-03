<?php

declare(strict_types=1);

namespace Crumbls\Tui\Display;

use Crumbls\Tui\Canvas\ShapePainter;
use Crumbls\Tui\Widget\WidgetRenderer;

interface DisplayExtension
{
    /**
     * @return ShapePainter[]
     */
    public function shapePainters(): array;

    /**
     * @return WidgetRenderer[]
     */
    public function widgetRenderers(): array;
}
