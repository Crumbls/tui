<?php

declare(strict_types=1);

namespace Crumbls\Tui\Terminal;

/**
 * Represents terminal dimensions.
 */
readonly class Size
{
    public function __construct(
        public int $width,
        public int $height
    ) {
        if ($width < 1 || $height < 1) {
            throw new \InvalidArgumentException('Terminal size must be positive');
        }
    }

    public function area(): int
    {
        return $this->width * $this->height;
    }

    public function equals(Size $other): bool
    {
        return $this->width === $other->width && $this->height === $other->height;
    }

    public function __toString(): string
    {
        return "{$this->width}x{$this->height}";
    }
}