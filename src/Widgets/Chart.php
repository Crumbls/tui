<?php

declare(strict_types=1);

namespace Crumbls\Tui\Widgets;

use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Widget;

class Chart extends Widget
{
    public function data(array $data): static
    {
        return $this->setAttribute('data', $data);
    }

    public function title(string $title): static
    {
        return $this->setAttribute('title', $title);
    }

    public function xAxis(array $labels): static
    {
        return $this->setAttribute('x_axis', $labels);
    }

    public function yAxis(array $labels): static
    {
        return $this->setAttribute('y_axis', $labels);
    }

    public function style(Style $style): static
    {
        return $this->setAttribute('style', $style);
    }

    public function render(): string
    {
        $title = $this->getAttribute('title', '');
        $data = $this->getAttribute('data', []);

        $output = '';
        
        if ($title) {
            $output .= $title . "\n";
        }

        // Simple ASCII chart implementation
        $output .= $this->renderSimpleChart($data);

        return $output;
    }

    protected function renderSimpleChart(array $data): string
    {
        if (empty($data)) {
            return "No data to display\n";
        }

        $max = max($data);
        $height = 10;
        $output = '';

        for ($row = $height; $row >= 0; $row--) {
            $line = '';
            foreach ($data as $value) {
                $normalizedValue = $max > 0 ? ($value / $max) * $height : 0;
                $line .= $normalizedValue >= $row ? '█' : ' ';
            }
            $output .= $line . "\n";
        }

        return $output;
    }
}