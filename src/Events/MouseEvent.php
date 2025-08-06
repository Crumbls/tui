<?php

namespace Crumbls\Tui\Events;

class MouseEvent
{
    public function __construct(
        public readonly int $x,
        public readonly int $y,
        public readonly string $button,
        public readonly string $action,
        public readonly bool $shift = false,
        public readonly bool $ctrl = false,
        public readonly bool $alt = false,
        public readonly mixed $clickedComponent = null
    ) {
    }

    public function isClick(): bool
    {
        return $this->action === 'press';
    }

    public function isRelease(): bool
    {
        return $this->action === 'release';
    }

    public function isDrag(): bool
    {
        return $this->action === 'drag';
    }

    public function isLeftButton(): bool
    {
        return $this->button === 'left';
    }

    public function isRightButton(): bool
    {
        return $this->button === 'right';
    }

    public function isMiddleButton(): bool
    {
        return $this->button === 'middle';
    }
}