<?php

namespace Crumbls\Tui\Layout;

class HorizontalLayout extends Layout
{
    protected int $spacing = 0;

    public function spacing(int $spacing): self
    {
        $this->spacing = $spacing;
        return $this;
    }

    public function calculate(array $components): array
    {
        if (empty($components)) {
            return [];
        }

        $positions = [];
        $availableWidth = $this->width - ($this->spacing * (count($components) - 1));
        $componentWidth = intval($availableWidth / count($components));
        $currentX = $this->x;

        foreach ($components as $i => $component) {
            // Last component gets remaining space
            $width = ($i === count($components) - 1) 
                ? $this->width - ($currentX - $this->x)
                : $componentWidth;

            $positions[$component->getId()] = [
                'component' => $component,
                'x' => $currentX,
                'y' => $this->y,
                'width' => $width,
                'height' => $this->height
            ];

            $currentX += $width + $this->spacing;
        }

        return $positions;
    }
}