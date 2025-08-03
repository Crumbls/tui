<?php

declare(strict_types=1);

namespace Crumbls\Tui\Event;

use Crumbls\Tui\Component\Component;

/**
 * Event fired when a component is clicked.
 */
final readonly class ClickEvent
{
    public function __construct(
        public int $screenX,
        public int $screenY,
        public int $relativeX,
        public int $relativeY,
        public Component $target,
        public string $button = 'left',
        public bool $ctrl = false,
        public bool $alt = false,
        public bool $shift = false,
    ) {
    }

    /**
     * Check if this is a left click.
     */
    public function isLeftClick(): bool
    {
        return $this->button === 'left';
    }

    /**
     * Check if this is a right click.
     */
    public function isRightClick(): bool
    {
        return $this->button === 'right';
    }

    /**
     * Check if this is a middle click.
     */
    public function isMiddleClick(): bool
    {
        return $this->button === 'middle';
    }

    /**
     * Check if any modifier key is pressed during the click.
     */
    public function hasModifiers(): bool
    {
        return $this->ctrl || $this->alt || $this->shift;
    }

    /**
     * Check if click is within the given bounds (relative to component).
     */
    public function isWithinBounds(int $width, int $height): bool
    {
        return $this->relativeX >= 0 
            && $this->relativeX < $width 
            && $this->relativeY >= 0 
            && $this->relativeY < $height;
    }

    /**
     * Get a string representation of the click event.
     */
    public function toString(): string
    {
        $modifiers = [];
        
        if ($this->ctrl) {
            $modifiers[] = 'Ctrl';
        }
        if ($this->alt) {
            $modifiers[] = 'Alt';
        }
        if ($this->shift) {
            $modifiers[] = 'Shift';
        }
        
        $modifierString = $modifiers ? implode('+', $modifiers) . '+' : '';
        
        return sprintf(
            '%s%s click at (%d,%d) relative (%d,%d)',
            $modifierString,
            ucfirst($this->button),
            $this->screenX,
            $this->screenY,
            $this->relativeX,
            $this->relativeY
        );
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}