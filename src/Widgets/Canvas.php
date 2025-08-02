<?php

declare(strict_types=1);

namespace Crumbls\Tui\Widgets;

use Crumbls\Tui\Widget;

class Canvas extends Widget
{
    public function width(int $width): static
    {
        return $this->setAttribute('width', $width);
    }

    public function height(int $height): static
    {
        return $this->setAttribute('height', $height);
    }

    public function shapes(array $shapes): static
    {
        return $this->setAttribute('shapes', $shapes);
    }

    public function render(): string
    {
        // Stub implementation
        return "Canvas widget (not yet implemented)\n";
    }
}