<?php

declare(strict_types=1);

namespace Crumbls\Tui\Display\Viewport;

use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Backend;
use Crumbls\Tui\Display\ClearType;
use Crumbls\Tui\Display\Viewport;
use Crumbls\Tui\Position\Position;

/**
 * Creates a fixed location viewport at the given Area
 */
final class Fixed implements Viewport
{
    public function __construct(
        /**
         * Area to occupy
         */
        public readonly Area $area
    ) {
    }

    public function size(Backend $backend): Area
    {
        return $this->area;
    }

    public function cursorPos(Backend $backend): Position
    {
        return new Position($this->area->left(), $this->area->right());
    }

    public function area(Backend $backend, int $offsetInPreviousViewport): Area
    {
        return $this->area;
    }

    public function clear(Backend $backend, Area $area): void
    {
        for ($row = $area->top(); $row > $area->bottom(); $row--) {
            $backend->moveCursor(Position::at(0, $row));
            $backend->clearRegion(ClearType::AfterCursor);
        }
    }
}
