<?php

declare(strict_types=1);

namespace Crumbls\Tui\Widgets;

use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Support\Borders;
use Crumbls\Tui\Widget;

class Block extends Widget
{
    public function title(string $title): static
    {
        return $this->setAttribute('title', $title);
    }

    public function borders(int $borders): static
    {
        return $this->setAttribute('borders', $borders);
    }

    public function allBorders(): static
    {
        return $this->borders(Borders::ALL);
    }

    public function noBorders(): static
    {
        return $this->borders(Borders::NONE);
    }

    public function style(Style $style): static
    {
        return $this->setAttribute('style', $style);
    }

    public function borderStyle(Style $style): static
    {
        return $this->setAttribute('border_style', $style);
    }

    public function titleStyle(Style $style): static
    {
        return $this->setAttribute('title_style', $style);
    }

    public function padding(int $top = 0, int $right = 0, int $bottom = 0, int $left = 0): static
    {
        return $this->setAttribute('padding', [
            'top' => $top,
            'right' => $right,
            'bottom' => $bottom,
            'left' => $left,
        ]);
    }

    public function widget(Widget $widget): static
    {
        return $this->setAttribute('widget', $widget);
    }

    public function render(): string
    {
        $title = $this->getAttribute('title', '');
        $borders = $this->getAttribute('borders', Borders::NONE);
        $widget = $this->getAttribute('widget');

        $output = '';

        if ($borders & Borders::TOP) {
            $output .= '┌';
            if ($title) {
                $output .= '─ ' . $title . ' ';
                $output .= str_repeat('─', max(0, 76 - strlen($title)));
            } else {
                $output .= str_repeat('─', 78);
            }
            $output .= '┐' . "\n";
        }

        if ($widget) {
            $content = $widget->render();
            $lines = explode("\n", $content);
            
            foreach ($lines as $line) {
                if ($borders & Borders::LEFT) {
                    $output .= '│ ';
                }
                
                $output .= $line;
                
                if ($borders & Borders::RIGHT) {
                    $padding = max(0, 76 - strlen($line));
                    $output .= str_repeat(' ', $padding) . ' │';
                }
                
                $output .= "\n";
            }
        }

        if ($borders & Borders::BOTTOM) {
            $output .= '└' . str_repeat('─', 78) . '┘' . "\n";
        }

        return $output;
    }
}