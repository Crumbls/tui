<?php

declare(strict_types=1);

namespace Crumbls\Tui\Events;

use Crumbls\Tui\Contracts\InputEventInterface;
use Crumbls\Tui\Events\Event;

/**
 * Represents a mouse input event.
 */
class MouseInputEvent extends Event implements InputEventInterface
{
    public function __construct(
        private string $action, // 'click', 'press', 'release', 'move', 'scroll'
        private int $x,
        private int $y,
        private string $button, // 'left', 'right', 'middle', 'none'
        private string $rawInput,
        private array $modifiers = []
    ) {
        parent::__construct();
    }

    public function getInputType(): string
    {
        return 'mouse';
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function getButton(): string
    {
        return $this->button;
    }

    public function getRawInput(): string
    {
        return $this->rawInput;
    }

    public function getModifiers(): array
    {
        return $this->modifiers;
    }

    public function hasModifier(string $modifier): bool
    {
        return in_array($modifier, $this->modifiers);
    }

    public function shouldHandle(): bool
    {
        // Only handle actual button events, not movement/drag
        if ($this->button === 'none') {
            return false; // Ignore drag/movement events
        }
        
        // Handle press, click, and release events
        if ($this->action === 'press' || $this->action === 'click' || $this->action === 'release') {
            return true;
        }
        
        // Handle scroll events
        if ($this->action === 'scroll') {
            return true;
        }
        
        return false;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'input_type' => $this->getInputType(),
            'action' => $this->action,
            'x' => $this->x,
            'y' => $this->y,
            'button' => $this->button,
            'raw_input' => $this->rawInput,
            'modifiers' => $this->modifiers,
        ]);
    }
}