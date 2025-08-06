<?php

namespace Crumbls\Tui\Terminal;

use Crumbls\Tui\Components\Contracts\Component;

class HitTester
{
    protected array $componentBounds = [];

    /**
     * Register a component's screen position for hit testing
     */
    public function registerComponent(Component $component, int $x, int $y, int $width, int $height): void
    {
        $this->componentBounds[$component->getId()] = [
            'component' => $component,
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height,
            'x2' => $x + $width - 1,
            'y2' => $y + $height - 1
        ];
    }

    /**
     * Find the component at the given screen coordinates
     */
    public function getComponentAt(int $x, int $y): ?Component
    {
        // Search from most recently registered (on top) to oldest (bottom)
        $bounds = array_reverse($this->componentBounds, true);
        
        foreach ($bounds as $componentId => $bound) {
            if ($this->isPointInBounds($x, $y, $bound)) {
                return $bound['component'];
            }
        }

        return null;
    }

    /**
     * Get all components at the given coordinates (for overlapping components)
     */
    public function getAllComponentsAt(int $x, int $y): array
    {
        $components = [];
        
        foreach ($this->componentBounds as $componentId => $bound) {
            if ($this->isPointInBounds($x, $y, $bound)) {
                $components[] = $bound['component'];
            }
        }

        return $components;
    }

    /**
     * Get component bounds by ID
     */
    public function getComponentBounds(string $componentId): ?array
    {
        return $this->componentBounds[$componentId] ?? null;
    }

    /**
     * Clear all registered components
     */
    public function clear(): void
    {
        $this->componentBounds = [];
    }

    /**
     * Get all registered components with their bounds
     */
    public function getAllBounds(): array
    {
        return $this->componentBounds;
    }

    /**
     * Check if a point is within the given bounds
     */
    protected function isPointInBounds(int $x, int $y, array $bound): bool
    {
        return $x >= $bound['x'] && 
               $x <= $bound['x2'] && 
               $y >= $bound['y'] && 
               $y <= $bound['y2'];
    }

    /**
     * Get the topmost component at coordinates (most recently rendered)
     */
    public function getTopComponentAt(int $x, int $y): ?Component
    {
        // Reverse order - last registered is on top
        $bounds = array_reverse($this->componentBounds, true);
        
        foreach ($bounds as $bound) {
            if ($this->isPointInBounds($x, $y, $bound)) {
                return $bound['component'];
            }
        }

        return null;
    }

    /**
     * Find components by type at coordinates
     */
    public function getComponentsByTypeAt(int $x, int $y, string $type): array
    {
        $components = [];
        
        foreach ($this->componentBounds as $bound) {
            if ($this->isPointInBounds($x, $y, $bound) && 
                $bound['component'] instanceof $type) {
                $components[] = $bound['component'];
            }
        }

        return $components;
    }
}