<?php

declare(strict_types=1);

namespace Crumbls\Tui\Widgets;

use Crumbls\Tui\Widget;

class Grid extends Widget
{
    public function columns(int $columns): static
    {
        return $this->setAttribute('columns', $columns);
    }

    public function rows(int $rows): static
    {
        return $this->setAttribute('rows', $rows);
    }

    public function widgets(array $widgets): static
    {
        return $this->setAttribute('widgets', $widgets);
    }

    public function render(): string
    {
        // Stub implementation
        return "Grid widget (not yet implemented)\n";
    }
}