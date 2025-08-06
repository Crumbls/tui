<?php

namespace Crumbls\Tui\Events;

use Crumbls\Tui\Terminal\Size;

class ResizeEvent
{
    protected Size $oldSize;
    protected Size $newSize;

    public function __construct(Size $oldSize, Size $newSize)
    {
        $this->oldSize = $oldSize;
        $this->newSize = $newSize;
    }

    public function getOldSize(): Size
    {
        return $this->oldSize;
    }

    public function getNewSize(): Size
    {
        return $this->newSize;
    }

    public function getOldWidth(): int
    {
        return $this->oldSize->getWidth();
    }

    public function getOldHeight(): int
    {
        return $this->oldSize->getHeight();
    }

    public function getNewWidth(): int
    {
        return $this->newSize->getWidth();
    }

    public function getNewHeight(): int
    {
        return $this->newSize->getHeight();
    }

    public function widthChanged(): bool
    {
        return $this->oldSize->getWidth() !== $this->newSize->getWidth();
    }

    public function heightChanged(): bool
    {
        return $this->oldSize->getHeight() !== $this->newSize->getHeight();
    }

    public function getDelta(): array
    {
        return [
            'width' => $this->newSize->getWidth() - $this->oldSize->getWidth(),
            'height' => $this->newSize->getHeight() - $this->oldSize->getHeight()
        ];
    }

    public function __toString(): string
    {
        return "ResizeEvent: {$this->oldSize} → {$this->newSize}";
    }
}