<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\ImageMagick;

use Crumbls\Tui\Display\DisplayExtension;
use Crumbls\Tui\Extension\ImageMagick\Shape\ImagePainter;
use Crumbls\Tui\Extension\ImageMagick\Widget\ImageRenderer;

final class ImageMagickExtension implements DisplayExtension
{
    public function __construct(private readonly ?ImageRegistry $imageRegistry = null)
    {
    }

    public function shapePainters(): array
    {
        return [
            new ImagePainter($this->imageRegistry())
        ];
    }

    public function widgetRenderers(): array
    {
        return [
            new ImageRenderer($this->imageRegistry())
        ];
    }

    private function imageRegistry(): ImageRegistry
    {
        return $this->imageRegistry ?? new ImageRegistry();
    }
}
