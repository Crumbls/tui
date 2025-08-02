<?php

declare(strict_types=1);

namespace Crumbls\Tui\Widgets;

use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Widget;
use Illuminate\Support\Collection;

class ListWidget extends Widget
{
    public function items(array|Collection $items): static
    {
        $items = $items instanceof Collection ? $items->toArray() : $items;
        
        return $this->setAttribute('items', $items);
    }

    public function selected(int $index): static
    {
        return $this->setAttribute('selected', $index);
    }

    public function style(Style $style): static
    {
        return $this->setAttribute('style', $style);
    }

    public function highlightStyle(Style $style): static
    {
        return $this->setAttribute('highlight_style', $style);
    }

    public function bullet(string $bullet): static
    {
        return $this->setAttribute('bullet', $bullet);
    }

    public function handleKey(string $key): void
    {
        $items = $this->getAttribute('items', []);
        $selected = $this->getAttribute('selected', 0);
        
        match ($key) {
            'j', "\x1b[B" => $this->selected(min($selected + 1, count($items) - 1)), // Down arrow or 'j'
            'k', "\x1b[A" => $this->selected(max($selected - 1, 0)), // Up arrow or 'k'
            default => null,
        };
    }

    public function render(): string
    {
        $items = $this->getAttribute('items', []);
        $selected = $this->getAttribute('selected');
        $bullet = $this->getAttribute('bullet', '• ');

        if (empty($items)) {
            return '';
        }

        $output = '';

        foreach ($items as $index => $item) {
            $isSelected = $selected === $index;
            $prefix = $isSelected ? '> ' : '  ';
            $content = $bullet . (string) $item;
            
            if ($isSelected) {
                $content = "\033[7m{$content}\033[0m"; // Reverse video
            }
            
            $output .= $prefix . $content . "\n";
        }

        return $output;
    }
}