<?php

declare(strict_types=1);

namespace Crumbls\Tui\Widgets;

use Crumbls\Tui\Contracts\WidgetInterface;

/**
 * Paragraph widget with ergonomic, fluent API.
 */
class Paragraph implements WidgetInterface
{
    protected string $text = '';
    protected ?int $width = null;
    protected ?int $height = null;

    public function __construct(string $text = '')
    {
        $this->text = $text;
    }

    public static function make(string $text = ''): static
    {
        return new static($text);
    }

    public function text(string $text): static
    {
        $this->text = $text;
        return $this;
    }

    public function setRegion(int $width, int $height): static
    {
        $this->width = $width;
        $this->height = $height;
        return $this;
    }

    public function render(): string
    {
        $lines = wordwrap($this->text, $this->width ?? 80, "\n", true);
        $linesArr = explode("\n", $lines);
        if ($this->height !== null) {
            $linesArr = array_slice($linesArr, 0, $this->height);
        }
        return implode("\n", $linesArr);
    }

    public function toArray(): array
    {
        return [
            'type' => 'paragraph',
            'text' => $this->text,
            'width' => $this->width,
            'height' => $this->height,
        ];
    }

    public static function makeFromArray(array $data): static
    {
        return static::make($data['text'] ?? '');
    }
}