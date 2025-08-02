<?php

declare(strict_types=1);

namespace Crumbls\Tui\Widgets;

use Crumbls\Tui\Widget;

class Sparkline extends Widget
{
    public function data(array $data): static
    {
        return $this->setAttribute('data', $data);
    }

    public function style(string $style): static
    {
        return $this->setAttribute('style', $style);
    }

    public function render(): string
    {
        $data = $this->getAttribute('data', []);
        
        if (empty($data)) {
            return '';
        }

        $chars = ['▁', '▂', '▃', '▄', '▅', '▆', '▇', '█'];
        $max = max($data);
        $min = min($data);
        $range = $max - $min;

        $output = '';
        
        foreach ($data as $value) {
            if ($range == 0) {
                $index = 0;
            } else {
                $normalized = ($value - $min) / $range;
                $index = (int) round($normalized * (count($chars) - 1));
            }
            
            $output .= $chars[$index];
        }

        return $output . "\n";
    }
}