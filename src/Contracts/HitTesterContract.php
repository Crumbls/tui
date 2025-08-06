<?php

namespace Crumbls\Tui\Contracts;

use Crumbls\Tui\Components\Component;

interface HitTesterContract
{
    /**
     * Register a component's position and bounds
     */
    public function registerComponent(Component $component, int $x, int $y, int $width, int $height): void;

    /**
     * Get the top component at specified coordinates
     */
    public function getComponentAt(int $x, int $y): ?Component;

    /**
     * Get all components at specified coordinates  
     */
    public function getAllComponentsAt(int $x, int $y): array;

    /**
     * Get the bounds for a specific component
     */
    public function getComponentBounds(string $componentId): ?array;

    /**
     * Clear all registered component bounds
     */
    public function clear(): void;

    /**
     * Get all registered component bounds
     */
    public function getAllBounds(): array;

    /**
     * Get the topmost component at coordinates
     */
    public function getTopComponentAt(int $x, int $y): ?Component;

    /**
     * Get components of specific type at coordinates
     */
    public function getComponentsByTypeAt(int $x, int $y, string $type): array;
}