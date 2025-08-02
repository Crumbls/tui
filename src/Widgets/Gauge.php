<?php

declare(strict_types=1);

namespace Crumbls\Tui\Widgets;

use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Widget;

class Gauge extends Widget
{
    public function percent(float $percent): static
    {
        return $this->setAttribute('percent', max(0, min(100, $percent)));
    }

    public function value(float $value, float $max = 100): static
    {
        $percent = $max > 0 ? ($value / $max) * 100 : 0;
        
        return $this->percent($percent);
    }

    public function label(string $label): static
    {
        return $this->setAttribute('label', $label);
    }

    public function style(Style $style): static
    {
        return $this->setAttribute('style', $style);
    }

    public function gaugeStyle(Style $style): static
    {
        return $this->setAttribute('gauge_style', $style);
    }

    public function labelStyle(Style $style): static
    {
        return $this->setAttribute('label_style', $style);
    }

    public function width(int $width): static
    {
        return $this->setAttribute('width', $width);
    }

    public function character(string $character): static
    {
        return $this->setAttribute('character', $character);
    }

    public function render(): string
    {
        $percent = $this->getAttribute('percent', 0);
        $label = $this->getAttribute('label');
        $width = $this->getAttribute('width', 50);
        $character = $this->getAttribute('character', '█');

        $filledWidth = (int) round(($percent / 100) * $width);
        $emptyWidth = $width - $filledWidth;

        $filled = str_repeat($character, $filledWidth);
        $empty = str_repeat('░', $emptyWidth);

        $gauge = "▐{$filled}{$empty}▌";
        
        if ($label) {
            $percentText = sprintf('%.1f%%', $percent);
            $gauge .= " {$label} ({$percentText})";
        }

        return $gauge . "\n";
    }
}