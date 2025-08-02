<?php

declare(strict_types=1);

namespace Crumbls\Tui\Events;

use Crumbls\Tui\Terminal\Size;

/**
 * Event fired when terminal is resized.
 */
class TerminalResizedEvent extends Event
{
    public function __construct(
        private Size $oldSize,
        private Size $newSize
    ) {
        parent::__construct();
    }

    public function getOldSize(): Size
    {
        return $this->oldSize;
    }

    public function getNewSize(): Size
    {
        return $this->newSize;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'old_size' => [
                'width' => $this->oldSize->width,
                'height' => $this->oldSize->height,
            ],
            'new_size' => [
                'width' => $this->newSize->width,
                'height' => $this->newSize->height,
            ],
        ]);
    }
}