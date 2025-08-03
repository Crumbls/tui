<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget;

use Crumbls\Tui\Canvas\CanvasContext;
use Crumbls\Tui\Canvas\ShapePainter;
use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Position\Position;
use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Widget\Widget;
use Crumbls\Tui\Widget\WidgetRenderer;

final class CanvasRenderer implements WidgetRenderer
{
    public function __construct(private readonly ShapePainter $painter)
    {
    }

    public function render(WidgetRenderer $renderer, Widget $widget, Buffer $buffer, Area $area): void
    {
        if (!$widget instanceof CanvasWidget) {
            return;
        }
        $painter = $widget->painter;

        $buffer->setStyle($area, Style::default()->bg($widget->backgroundColor));
        $width = $area->width;

        $context = CanvasContext::new(
            $this->painter,
            $area->width,
            $area->height,
            $widget->xBounds,
            $widget->yBounds,
            $widget->marker,
        );

        $saveLayer = false;
        foreach ($widget->shapes as $shape) {
            $context->draw($shape);
            $context->saveLayer();
            $saveLayer = true;
        }

        if ($saveLayer) {
            // if shapes were added then save the layer before
            // calling the closure
            $context->saveLayer();
        }

        if ($painter !== null) {
            $painter($context);
        }
        $context->finish();

        foreach ($context->layers as $layer) {
            foreach ($layer->chars as $index => $char) {
                if ($char === ' ' || $char === "\u{2800}") {
                    continue;
                }
                $color = $layer->colors[$index];
                $x = ($index % $width) + $area->left();
                $y = ($index / $width) + $area->top();
                $cell = $buffer->get(Position::at(
                    max(0, $x),
                    max(0, (int) $y)
                ))->setChar($char);
                $cell->fg = $color->fg;
                $cell->bg = $color->bg;
            }
        }

        foreach ($context->labels->withinBounds($widget->xBounds, $widget->yBounds) as $label) {
            $x = (int) (
                ((
                    $label->position->x - $widget->xBounds->min
                ) * ($area->width - 1) / $widget->xBounds->length()) + $area->left()
            );
            $y = (int) (
                ((
                    $widget->yBounds->max - $label->position->y
                ) * ($area->height - 1) / $widget->yBounds->length()) + $area->top()
            );
            $buffer->putLine(
                Position::at(max(0, $x), max(0, $y)),
                $label->line,
                $area->right() - $x
            );
        }
    }
}
