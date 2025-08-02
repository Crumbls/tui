<?php

declare(strict_types=1);

namespace Crumbls\Tui\Layout;

use Crumbls\Tui\Contracts\WidgetInterface;

class CssLayout
{
    protected array $children = [];
    protected string $direction = 'vertical';

    public static function flexColumn(): static
    {
        return new static('vertical');
    }

    public static function flexRow(): static
    {
        return new static('horizontal');
    }

    public function __construct(string $direction = 'vertical')
    {
        $this->direction = $direction;
    }

    public function navbar(WidgetInterface $widget, int $height = 3): static
    {
        $this->children[] = [
            'widget' => $widget,
            'type' => 'navbar',
            'height' => $height,
            'flex' => false
        ];
        return $this;
    }

    public function content(WidgetInterface $widget): static
    {
        $this->children[] = [
            'widget' => $widget,
            'type' => 'content', 
            'height' => 0,
            'flex' => true
        ];
        return $this;
    }

    public function sidebar(WidgetInterface $widget, int $width = 25): static
    {
        $this->children[] = [
            'widget' => $widget,
            'type' => 'sidebar',
            'width' => $width,
            'flex' => false
        ];
        return $this;
    }

    public function render(int $terminalWidth = 80, int $terminalHeight = 24): string
    {
        if (empty($this->children)) {
            return '';
        }

        if ($this->direction === 'vertical') {
            return $this->renderVertical($terminalWidth, $terminalHeight);
        } else {
            return $this->renderHorizontal($terminalWidth, $terminalHeight);
        }
    }

    protected function renderVertical(int $width, int $height): string
    {
        $output = '';
        $remainingHeight = $height;

        // Calculate fixed heights first
        $flexChildren = [];
        foreach ($this->children as $index => $child) {
            if (!$child['flex']) {
                $childHeight = $child['height'] ?? 3;
                $remainingHeight -= $childHeight;
            } else {
                $flexChildren[] = $index;
            }
        }

        // Distribute remaining height among flex children
        $flexHeight = count($flexChildren) > 0 ? (int)($remainingHeight / count($flexChildren)) : 0;

        foreach ($this->children as $index => $child) {
            $childHeight = $child['flex'] ? $flexHeight : ($child['height'] ?? 3);
            $widget = $child['widget'];
            
            $childOutput = $widget->render();
            $lines = explode("\n", trim($childOutput));
            
            // Limit to allocated height
            $lines = array_slice($lines, 0, $childHeight);
            
            // Pad to full height if needed
            while (count($lines) < $childHeight) {
                $lines[] = '';
            }
            
            $output .= implode("\n", $lines);
            if ($index < count($this->children) - 1) {
                $output .= "\n";
            }
        }

        return $output;
    }

    protected function renderHorizontal(int $width, int $height): string
    {
        // Side-by-side rendering (for sidebar + content)
        $childOutputs = [];
        $widths = [];
        $remainingWidth = $width;

        // Calculate widths
        foreach ($this->children as $child) {
            if (!$child['flex']) {
                $childWidth = $child['width'] ?? 25;
                $widths[] = $childWidth;
                $remainingWidth -= $childWidth;
            } else {
                $widths[] = $remainingWidth; // Give remaining space to flex child
            }
        }

        // Render each child
        foreach ($this->children as $index => $child) {
            $widget = $child['widget'];
            $childOutput = $widget->render();
            $lines = explode("\n", trim($childOutput));
            $childOutputs[$index] = $lines;
        }

        $output = '';
        $maxLines = max(array_map('count', $childOutputs));

        for ($line = 0; $line < $maxLines; $line++) {
            $lineOutput = '';
            foreach ($childOutputs as $index => $lines) {
                $childWidth = $widths[$index];
                $lineText = $lines[$line] ?? '';
                
                // Truncate and pad to exact width
                $lineText = substr($lineText, 0, $childWidth);
                $lineText = str_pad($lineText, $childWidth);
                $lineOutput .= $lineText;
            }
            $output .= rtrim($lineOutput) . "\n";
        }

        return rtrim($output);
    }
}