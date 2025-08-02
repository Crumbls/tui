<?php

declare(strict_types=1);

namespace Crumbls\Tui\Rendering;

/**
 * Buffer cell containing character and styling information.
 * Based on PhpTui's Cell implementation but adapted to our conventions.
 */
class Cell
{
    public function __construct(
        public string $char = ' ',
        public array $fgColor = [255, 255, 255], // RGB
        public array $bgColor = [0, 0, 0],       // RGB
        public int $modifiers = 0                 // Bitfield for styling
    ) {
    }

    public static function empty(): self
    {
        return new self(' ');
    }

    public static function fromChar(string $char): self
    {
        return new self($char);
    }

    public function setChar(string $char): self
    {
        $this->char = $char;
        return $this;
    }

    public function setForegroundColor(int $r, int $g, int $b): self
    {
        $this->fgColor = [$r, $g, $b];
        return $this;
    }

    public function setBackgroundColor(int $r, int $g, int $b): self
    {
        $this->bgColor = [$r, $g, $b];
        return $this;
    }

    public function setModifiers(int $modifiers): self
    {
        $this->modifiers = $modifiers;
        return $this;
    }

    /**
     * Check if this cell equals another cell (for diff comparison).
     */
    public function equals(Cell $other): bool
    {
        return $this->char === $other->char
            && $this->fgColor === $other->fgColor
            && $this->bgColor === $other->bgColor
            && $this->modifiers === $other->modifiers;
    }

    /**
     * Clone this cell.
     */
    public function clone(): self
    {
        return new self(
            $this->char,
            $this->fgColor,
            $this->bgColor,
            $this->modifiers
        );
    }

    public function __toString(): string
    {
        return sprintf(
            'Cell("%s", fg:[%d,%d,%d], bg:[%d,%d,%d], mod:%d)',
            $this->char,
            $this->fgColor[0], $this->fgColor[1], $this->fgColor[2],
            $this->bgColor[0], $this->bgColor[1], $this->bgColor[2],
            $this->modifiers
        );
    }
}