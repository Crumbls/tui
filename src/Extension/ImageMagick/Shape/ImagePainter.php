<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\ImageMagick\Shape;

use ImagickPixel;
use Crumbls\Tui\Canvas\Label;
use Crumbls\Tui\Canvas\Painter;
use Crumbls\Tui\Canvas\Shape;
use Crumbls\Tui\Canvas\ShapePainter;
use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\Color\RgbColor;
use Crumbls\Tui\Extension\Core\Shape\LineShape;
use Crumbls\Tui\Extension\ImageMagick\ImageRegistry;
use Crumbls\Tui\Position\FloatPosition;
use Crumbls\Tui\Text\Line as PhpTuiLine;

final class ImagePainter implements ShapePainter
{
    private readonly ImageRegistry $registry;

    public function __construct(ImageRegistry $registry = null)
    {
        $this->registry = $registry ?? new ImageRegistry();
    }

    public function draw(ShapePainter $shapePainter, Painter $painter, Shape $shape): void
    {
        if (!$shape instanceof ImageShape) {
            return;
        }

        if (!extension_loaded('imagick')) {
            $shapePainter->draw(
                $shapePainter,
                $painter,
                LineShape::fromScalars(
                    $painter->context->xBounds->min + 1,
                    $painter->context->yBounds->min + 1,
                    $painter->context->xBounds->max - 1,
                    $painter->context->yBounds->max - 1
                )->color(AnsiColor::White)
            );
            $shapePainter->draw(
                $shapePainter,
                $painter,
                LineShape::fromScalars(
                    $painter->context->xBounds->min + 1,
                    $painter->context->yBounds->max - 1,
                    $painter->context->xBounds->max - 1,
                    $painter->context->yBounds->min + 1,
                )->color(AnsiColor::White)
            );
            $painter->context->labels->add(
                new Label(FloatPosition::at(0, 0), PhpTuiLine::fromString('Imagick extension not loaded!'))
            );

            return;
        }

        $image = $this->registry->load(
            $shape->path,
        );
        $geo = $image->getImageGeometry();

        /** @var ImagickPixel[] $pixels */
        foreach ($image->getPixelIterator() as $y => $pixels) {
            foreach ($pixels as $x => $pixel) {
                $point = $painter->getPoint(
                    FloatPosition::at(
                        $shape->position->x + $x,
                        $shape->position->y + $geo['height'] - (int) $y - 1
                    )
                );
                if (null === $point) {
                    continue;
                }
                $rgb = $pixel->getColor();
                $painter->paint($point, RgbColor::fromRgb(
                    $rgb['r'],
                    $rgb['g'],
                    $rgb['b']
                ));
            }
        }

    }
}
