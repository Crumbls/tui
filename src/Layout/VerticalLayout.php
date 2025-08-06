<?php

namespace Crumbls\Tui\Layout;

class VerticalLayout extends Layout
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
        $availableHeight = $this->height - ($this->spacing * (count($components) - 1));
        $componentHeight = intval($availableHeight / count($components));
        $currentY = $this->y;

        foreach ($components as $i => $component) {
            // Last component gets remaining space
            $height = ($i === count($components) - 1) 
                ? $this->height - ($currentY - $this->y)
                : $componentHeight;

            $positions[$component->getId()] = [
                'component' => $component,
                'x' => $this->x,
                'y' => $currentY,
                'width' => $this->width,
                'height' => $height
            ];

            $currentY += $height + $this->spacing;
        }

        return $positions;
    }
}