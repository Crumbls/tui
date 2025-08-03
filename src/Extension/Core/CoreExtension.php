<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core;

use Crumbls\Tui\Display\DisplayExtension;
use Crumbls\Tui\Extension\Core\Shape\CirclePainter;
use Crumbls\Tui\Extension\Core\Shape\ClosurePainter;
use Crumbls\Tui\Extension\Core\Shape\LinePainter;
use Crumbls\Tui\Extension\Core\Shape\MapPainter;
use Crumbls\Tui\Extension\Core\Shape\PointsPainter;
use Crumbls\Tui\Extension\Core\Shape\RectanglePainter;
use Crumbls\Tui\Extension\Core\Shape\SpritePainter;
use Crumbls\Tui\Extension\Core\Widget\BarChartRenderer;
use Crumbls\Tui\Extension\Core\Widget\BlockRenderer;
use Crumbls\Tui\Extension\Core\Widget\BufferWidgetRenderer;
use Crumbls\Tui\Extension\Core\Widget\ChartRenderer;
use Crumbls\Tui\Extension\Core\Widget\CompositeRenderer;
use Crumbls\Tui\Extension\Core\Widget\GaugeRenderer;
use Crumbls\Tui\Extension\Core\Widget\GridRenderer;
use Crumbls\Tui\Extension\Core\Widget\ListRenderer;
use Crumbls\Tui\Extension\Core\Widget\ParagraphRenderer;
use Crumbls\Tui\Extension\Core\Widget\ScrollbarRenderer;
use Crumbls\Tui\Extension\Core\Widget\SparklineRenderer;
use Crumbls\Tui\Extension\Core\Widget\TableRenderer;
use Crumbls\Tui\Extension\Core\Widget\TabsRenderer;

final class CoreExtension implements DisplayExtension
{
    public function shapePainters(): array
    {
        return [
            new CirclePainter(),
            new LinePainter(),
            new MapPainter(),
            new PointsPainter(),
            new RectanglePainter(),
            new SpritePainter(),
            new ClosurePainter(),
        ];
    }

    public function widgetRenderers(): array
    {
        return [
            new BlockRenderer(),
            new ParagraphRenderer(),
            new ChartRenderer(),
            new GridRenderer(),
            new ListRenderer(),
            new BufferWidgetRenderer(),
            new TableRenderer(),
            new GaugeRenderer(),
            new BarChartRenderer(),
            new ScrollbarRenderer(),
            new CompositeRenderer(),
            new TabsRenderer(),
            new SparklineRenderer(),
        ];
    }
}
