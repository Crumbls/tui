<?php

namespace Crumbls\Tui\Layout;

class AbsoluteLayout extends Layout
{
    protected array $positions = [];

    /**
     * Set absolute position for a component
     */
    public function position($component, int $x, int $y, int $width, int $height): self
    {
        $componentId = is_object($component) ? $component->getId() : $component;
        $this->positions[$componentId] = [
            'x' => $this->x + $x,
            'y' => $this->y + $y,
            'width' => $width,
            'height' => $height
        ];

        return $this;
    }

    public function calculate(array $components): array
    {
        $positions = [];

        foreach ($components as $component) {
            $componentId = $component->getId();
            if (isset($this->positions[$componentId])) {
                $positions[$componentId] = array_merge($this->positions[$componentId], ['component' => $component]);
            } else {
                // Default position if not specified
                $positions[$componentId] = [
                    'component' => $component,
                    'x' => $this->x,
                    'y' => $this->y,
                    'width' => 10,
                    'height' => 3
                ];
            }
        }

        return $positions;
    }
}