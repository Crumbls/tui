<?php

declare(strict_types=1);

namespace Crumbls\Tui\Contracts;

/**
 * Interface for rendering components to terminal output.
 */
interface RendererInterface
{
    /**
     * Render a component tree and return the output string.
     */
    public function render(?ComponentInterface $rootComponent = null): string;

    /**
     * Set the root component to render.
     */
    public function setRootComponent(?ComponentInterface $component): static;

    /**
     * Get the current root component.
     */
    public function getRootComponent(): ?ComponentInterface;

    /**
     * Set the renderer size.
     */
    public function setSize(int $width, int $height): static;

    /**
     * Get the renderer width.
     */
    public function getWidth(): int;

    /**
     * Get the renderer height.
     */
    public function getHeight(): int;

    /**
     * Mark the renderer as needing a refresh.
     */
    public function markDirty(): static;

    /**
     * Check if the renderer needs a refresh.
     */
    public function isDirty(): bool;

    /**
     * Clear the dirty flag.
     */
    public function clearDirty(): static;

    /**
     * Get the buffer for this renderer.
     */
    public function getBuffer(): BufferInterface;
}