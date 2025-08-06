<?php

namespace Crumbls\Tui\Contracts;

interface ScreenContract
{
    /**
     * Clear the screen
     */
    public function clear(): self;

    /**
     * Get screen width
     */
    public function getWidth(): int;

    /**
     * Get screen height
     */
    public function getHeight(): int;

    /**
     * Draw a character at specified position
     */
    public function drawChar(int $x, int $y, string $char, ?string $color = null): self;

    /**
     * Draw a string at specified position
     */
    public function drawString(int $x, int $y, string $text, ?string $color = null): self;

    /**
     * Draw a box border
     */
    public function drawBox(int $x, int $y, int $width, int $height, array $chars = null): self;

    /**
     * Fill a rectangular area
     */
    public function fillRect(int $x, int $y, int $width, int $height, string $char = ' ', ?string $color = null): self;

    /**
     * Render the screen buffer to terminal
     */
    public function render(): self;

    /**
     * Get the screen buffer
     */
    public function getBuffer(): array;

    /**
     * Get the color buffer
     */
    public function getColorBuffer(): array;
}