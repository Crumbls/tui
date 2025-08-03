<?php

declare(strict_types=1);

namespace Crumbls\Tui\Event;

use Crumbls\Tui\Component\Component;

/**
 * Event fired when a key is pressed.
 */
final readonly class KeyPressEvent
{
    public function __construct(
        public string $key,
        public bool $ctrl = false,
        public bool $alt = false,
        public bool $shift = false,
        public ?Component $target = null,
    ) {
    }

    /**
     * Check if the event matches a key combination.
     * 
     * Examples:
     * - $event->is('Enter')
     * - $event->is('ctrl+a')
     * - $event->is('shift+Tab')
     * - $event->is('ctrl+shift+z')
     */
    public function is(string $combination): bool
    {
        $parts = array_map('trim', explode('+', strtolower($combination)));
        $expectedKey = array_pop($parts);
        
        // Check if key matches
        if (strtolower($this->key) !== strtolower($expectedKey)) {
            return false;
        }
        
        // Check modifiers
        $expectedCtrl = in_array('ctrl', $parts) || in_array('control', $parts);
        $expectedAlt = in_array('alt', $parts);
        $expectedShift = in_array('shift', $parts);
        
        return $this->ctrl === $expectedCtrl 
            && $this->alt === $expectedAlt 
            && $this->shift === $expectedShift;
    }

    /**
     * Check if any modifier key is pressed.
     */
    public function hasModifiers(): bool
    {
        return $this->ctrl || $this->alt || $this->shift;
    }

    /**
     * Get a string representation of the key combination.
     */
    public function toString(): string
    {
        $parts = [];
        
        if ($this->ctrl) {
            $parts[] = 'Ctrl';
        }
        if ($this->alt) {
            $parts[] = 'Alt';
        }
        if ($this->shift) {
            $parts[] = 'Shift';
        }
        
        $parts[] = $this->key;
        
        return implode('+', $parts);
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}