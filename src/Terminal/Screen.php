<?php

namespace Crumbls\Tui\Terminal;

use Crumbls\Tui\Contracts\ScreenContract;
use Crumbls\Tui\Contracts\TerminalContract;

class Screen implements ScreenContract
{
    protected array $buffer = [];
    protected array $colorBuffer = [];
    protected array $previousBuffer = [];
    protected array $previousColorBuffer = [];
    protected int $width;
    protected int $height;
    protected TerminalContract $terminal;
    protected bool $firstRender = true;

    public function __construct(TerminalContract $terminal)
    {
        $this->terminal = $terminal;
        $this->width = $terminal->getWidth();
        $this->height = $terminal->getHeight();
        $this->clear();
    }

    // =================== BUFFER MANAGEMENT ===================

    public function clear(): self
    {
        $this->buffer = array_fill(0, $this->height, array_fill(0, $this->width, ' '));
        $this->colorBuffer = array_fill(0, $this->height, array_fill(0, $this->width, null));
        
        // Initialize previous buffers on first clear
        if (empty($this->previousBuffer)) {
            $this->previousBuffer = array_fill(0, $this->height, array_fill(0, $this->width, ''));
            $this->previousColorBuffer = array_fill(0, $this->height, array_fill(0, $this->width, ''));
        }
        
        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    // =================== DRAWING ===================

    public function drawChar(int $x, int $y, string $char, ?string $color = null): self
    {
        if ($this->isValidPosition($x, $y)) {
            $this->buffer[$y][$x] = mb_substr($char, 0, 1);
            $this->colorBuffer[$y][$x] = $color;
        }
        return $this;
    }

    public function drawString(int $x, int $y, string $text, ?string $color = null): self
    {
        $chars = mb_str_split($text);
        foreach ($chars as $i => $char) {
            $this->drawChar($x + $i, $y, $char, $color);
        }
        return $this;
    }

    public function drawBox(int $x, int $y, int $width, int $height, array $chars = null): self
    {
        $chars = $chars ?? [
            'tl' => '┌', 'tr' => '┐', 'bl' => '└', 'br' => '┘',
            'h' => '─', 'v' => '│'
        ];

        // Top border
        $this->drawChar($x, $y, $chars['tl']);
        for ($i = 1; $i < $width - 1; $i++) {
            $this->drawChar($x + $i, $y, $chars['h']);
        }
        $this->drawChar($x + $width - 1, $y, $chars['tr']);

        // Side borders
        for ($i = 1; $i < $height - 1; $i++) {
            $this->drawChar($x, $y + $i, $chars['v']);
            $this->drawChar($x + $width - 1, $y + $i, $chars['v']);
        }

        // Bottom border
        if ($height > 1) {
            $this->drawChar($x, $y + $height - 1, $chars['bl']);
            for ($i = 1; $i < $width - 1; $i++) {
                $this->drawChar($x + $i, $y + $height - 1, $chars['h']);
            }
            $this->drawChar($x + $width - 1, $y + $height - 1, $chars['br']);
        }

        return $this;
    }

    public function fillRect(int $x, int $y, int $width, int $height, string $char = ' ', ?string $color = null): self
    {
        for ($row = 0; $row < $height; $row++) {
            for ($col = 0; $col < $width; $col++) {
                $this->drawChar($x + $col, $y + $row, $char, $color);
            }
        }
        return $this;
    }

    // =================== RENDERING ===================

    public function render(): self
    {
        if ($this->firstRender) {
            // First render - clear screen and draw everything
            $this->terminal->clear();
            $this->renderFullScreen();
            $this->firstRender = false;
        } else {
            // Subsequent renders - only update changed cells
            $this->renderDifferences();
        }

        // Update previous buffer for next comparison
        $this->previousBuffer = $this->buffer;
        $this->previousColorBuffer = $this->colorBuffer;

        flush(); // Force output
        return $this;
    }

    protected function renderFullScreen(): void
    {
        $this->terminal->moveCursor(1, 1);
        $output = '';
        $currentColor = null;

        for ($y = 0; $y < $this->height; $y++) {
            if ($y > 0) {
                $output .= "\n";
            }
            
            for ($x = 0; $x < $this->width; $x++) {
                $char = $this->buffer[$y][$x];
                $color = $this->colorBuffer[$y][$x];

                // Handle color changes
                if ($color !== $currentColor) {
                    if ($currentColor !== null) {
                        $output .= "\e[0m";
                    }
                    if ($color !== null) {
                        $output .= $color;
                    }
                    $currentColor = $color;
                }

                $output .= $char;
            }
        }

        // Reset colors at end
        if ($currentColor !== null) {
            $output .= "\e[0m";
        }

        echo $output;
    }

    protected function renderDifferences(): void
    {
        $currentColor = null;

        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                $char = $this->buffer[$y][$x];
                $color = $this->colorBuffer[$y][$x];
                $prevChar = $this->previousBuffer[$y][$x] ?? '';
                $prevColor = $this->previousColorBuffer[$y][$x] ?? '';

                // Only update if character or color changed
                if ($char !== $prevChar || $color !== $prevColor) {
                    // Move cursor to this position
                    $this->terminal->moveCursor($x + 1, $y + 1);

                    // Handle color changes
                    if ($color !== $currentColor) {
                        if ($currentColor !== null) {
                            echo "\e[0m"; // Reset
                        }
                        if ($color !== null) {
                            echo $color;
                        }
                        $currentColor = $color;
                    }

                    echo $char;
                }
            }
        }

        // Reset colors at end
        if ($currentColor !== null) {
            echo "\e[0m";
        }
    }

    // =================== UTILITIES ===================

    protected function isValidPosition(int $x, int $y): bool
    {
        return $x >= 0 && $x < $this->width && $y >= 0 && $y < $this->height;
    }

    public function getBuffer(): array
    {
        return $this->buffer;
    }

    public function getColorBuffer(): array
    {
        return $this->colorBuffer;
    }

    // =================== CLIPPING ===================

    public function clip(int $x, int $y, int $width, int $height): ScreenClip
    {
        return new ScreenClip($this, $x, $y, $width, $height);
    }
}

// Helper class for clipped drawing operations
class ScreenClip
{
    public function __construct(
        protected Screen $screen,
        protected int $clipX,
        protected int $clipY,
        protected int $clipWidth,
        protected int $clipHeight
    ) {
    }

    public function drawString(int $x, int $y, string $text, ?string $color = null): self
    {
        // Only draw within clipping bounds
        $absoluteX = $this->clipX + $x;
        $absoluteY = $this->clipY + $y;

        if ($absoluteX >= $this->clipX && $absoluteX < $this->clipX + $this->clipWidth &&
            $absoluteY >= $this->clipY && $absoluteY < $this->clipY + $this->clipHeight) {
            $this->screen->drawString($absoluteX, $absoluteY, $text, $color);
        }

        return $this;
    }

    public function drawBox(int $x, int $y, int $width, int $height): self
    {
        $this->screen->drawBox($this->clipX + $x, $this->clipY + $y, $width, $height);
        return $this;
    }
}