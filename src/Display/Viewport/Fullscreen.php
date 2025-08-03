<?php

declare(strict_types=1);

namespace Crumbls\Tui\Display\Viewport;

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Backend;
use Crumbls\Tui\Display\ClearType;
use Crumbls\Tui\Display\Viewport;
use Crumbls\Tui\Position\Position;

/**
 * Viewport that occupies the entire screen.
 */
final class Fullscreen implements Viewport
{
    public function size(Backend $backend): Area
    {
        return $backend->size();
    }

    public function cursorPos(Backend $backend): Position
    {
        return new Position(0, 0);
    }

    public function area(Backend $backend, int $offsetInPreviousViewport): Area
    {
        return $this->size($backend);
    }

    public function clear(Backend $backend, Area $area): void
    {
        $backend->clearRegion(ClearType::ALL);
    }
}
