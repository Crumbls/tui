<?php

namespace Crumbls\Tui\Terminal;

class Terminal
{
    protected int $width;
    protected int $height;
    protected bool $rawMode = false;

    public function __construct(?int $width = null, ?int $height = null)
    {
        $this->detectDimensions($width, $height);
    }

    public function __destruct()
    {
        $this->exitRawMode();
    }

    // =================== DIMENSIONS ===================

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getDimensions(): array
    {
        return [$this->width, $this->height];
    }

    protected function detectDimensions(?int $width, ?int $height): void
    {
        if ($width && $height) {
            $this->width = $width;
            $this->height = $height;
            return;
        }

        // Try to get terminal size
        $size = $this->getTerminalSize();
        $this->width = $width ?? $size[0] ?? 80;
        $this->height = $height ?? $size[1] ?? 24;
    }

    protected function getTerminalSize(): array
    {
        // Try different methods to get terminal size
        if (function_exists('exec')) {
            // Method 1: tput
            exec('tput cols 2>/dev/null', $cols, $colsReturn);
            exec('tput lines 2>/dev/null', $lines, $linesReturn);
            
            if ($colsReturn === 0 && $linesReturn === 0) {
                return [(int)($cols[0] ?? 80), (int)($lines[0] ?? 24)];
            }

            // Method 2: stty
            exec('stty size 2>/dev/null', $output, $return);
            if ($return === 0 && isset($output[0])) {
                $size = explode(' ', trim($output[0]));
                if (count($size) >= 2) {
                    return [(int)$size[1], (int)$size[0]]; // cols, rows
                }
            }
        }

        // Fallback to common terminal size
        return [80, 24];
    }

    // =================== RAW MODE ===================

    public function enterRawMode(): self
    {
        if (!$this->rawMode && function_exists('system')) {
            // Save current terminal state and enter raw mode
            system('stty -echo -icanon min 1 time 0 2>/dev/null');
            $this->rawMode = true;
        }

        return $this;
    }

    public function exitRawMode(): self
    {
        if ($this->rawMode && function_exists('system')) {
            // Restore normal terminal mode
            system('stty echo icanon 2>/dev/null');
            $this->rawMode = false;
        }

        return $this;
    }

    public function isRawMode(): bool
    {
        return $this->rawMode;
    }

    // =================== TERMINAL CONTROL ===================

    public function clear(): self
    {
        echo "\e[2J\e[H";
        return $this;
    }

    public function moveCursor(int $x, int $y): self
    {
        echo "\e[{$y};{$x}H";
        return $this;
    }

    public function hideCursor(): self
    {
        echo "\e[?25l";
        return $this;
    }

    public function showCursor(): self
    {
        echo "\e[?25h";
        return $this;
    }

    public function enableAlternateScreen(): self
    {
        echo "\e[?1049h";
        return $this;
    }

    public function disableAlternateScreen(): self
    {
        echo "\e[?1049l";
        return $this;
    }
}