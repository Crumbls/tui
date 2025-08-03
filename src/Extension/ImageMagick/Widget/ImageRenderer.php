<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\ImageMagick\Widget;

use Imagick;
use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;
use Crumbls\Tui\Extension\ImageMagick\ImageRegistry;
use Crumbls\Tui\Extension\ImageMagick\Shape\ImageShape;
use Crumbls\Tui\Widget\Widget;
use Crumbls\Tui\Widget\WidgetRenderer;

final class ImageRenderer implements WidgetRenderer
{
    public function __construct(private readonly ImageRegistry $registry)
    {
    }

    public function render(WidgetRenderer $renderer, Widget $widget, Buffer $buffer, Area $area): void
    {
        if (!$widget instanceof ImageWidget) {
            return;
        }

        if (class_exists(Imagick::class)) {
            $image = $this->registry->load($widget->path);
            $geo = $image->getImageGeometry();
        } else {
            // otherwise extension not loaded, image shape will show a
            // placeholder!
            $geo = [ 'width' => 100, 'height' => 100 ];
        }

        $renderer->render($renderer, CanvasWidget::fromIntBounds(
            0,
            $geo['width'] - 1,
            0,
            $geo['height'],
        )->marker($widget->marker ?? Marker::HalfBlock)->draw(ImageShape::fromPath(
            $widget->path
        )), $buffer, $area);
    }
}
