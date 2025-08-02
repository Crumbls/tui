<?php

declare(strict_types=1);

namespace Crumbls\Tui\Widgets;

use Crumbls\Tui\Contracts\WidgetInterface;

class Divider implements WidgetInterface
{
    protected string $char;
    protected ?int $width = null;

    public function __construct(string $char = '-')
    {
        $this->char = $char;
    }

    public static function make(string $char = '-'): static
    {
        return new static($char);
    }

    public function setRegion(int $width, int $height): static
    {
        $this->width = $width;
        return $this;
    }

    public function render(): string
    {
        $width = $this->width ?? 80;
        return str_repeat($this->char, $width);
    }

    public function toArray(): array
    {
        return [
            'type' => 'divider',
            'char' => $this->char,
            'width' => $this->width,
        ];
    }
}
