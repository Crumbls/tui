<?php

declare(strict_types=1);

namespace Crumbls\Tui\Style;

class Style
{
    protected ?string $foreground = null;
    protected ?string $background = null;
    protected array $modifiers = [];

    public function __construct(
        ?string $foreground = null,
        ?string $background = null,
        array $modifiers = []
    ) {
        $this->foreground = $foreground;
        $this->background = $background;
        $this->modifiers = $modifiers;
    }

    public static function default(): static
    {
        return new static();
    }

    public static function make(?string $foreground = null, ?string $background = null): static
    {
        return new static($foreground, $background);
    }

    public function fg(string $color): static
    {
        $this->foreground = $color;

        return $this;
    }

    public function bg(string $color): static
    {
        $this->background = $color;

        return $this;
    }

    public function bold(): static
    {
        $this->modifiers[] = 'bold';

        return $this;
    }

    public function italic(): static
    {
        $this->modifiers[] = 'italic';

        return $this;
    }

    public function underline(): static
    {
        $this->modifiers[] = 'underline';

        return $this;
    }

    public function getForeground(): ?string
    {
        return $this->foreground;
    }

    public function getBackground(): ?string
    {
        return $this->background;
    }

    public function getModifiers(): array
    {
        return $this->modifiers;
    }
}