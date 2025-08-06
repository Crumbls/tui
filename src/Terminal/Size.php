<?php

namespace Crumbls\Tui\Terminal;

class Size
{
    protected int $width;
    protected int $height;
    protected static ?Size $instance = null;

    public function __construct(int $width = 80, int $height = 24)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public static function detect(): self
    {
        $width = 80;
        $height = 24;

        // Try multiple methods to get terminal size
        if (function_exists('exec')) {
            // Method 1: Use stty
            $sttyOutput = null;
            exec('stty size 2>/dev/null', $sttyOutput, $returnVar);
            if ($returnVar === 0 && !empty($sttyOutput[0])) {
                $parts = explode(' ', trim($sttyOutput[0]));
                if (count($parts) === 2) {
                    $height = (int) $parts[0];
                    $width = (int) $parts[1];
                }
            }
            
            // Method 2: Use tput as fallback
            if ($width === 80 && $height === 24) {
                $tputWidth = null;
                $tputHeight = null;
                exec('tput cols 2>/dev/null', $tputWidth, $returnVar1);
                exec('tput lines 2>/dev/null', $tputHeight, $returnVar2);
                
                if ($returnVar1 === 0 && !empty($tputWidth[0])) {
                    $width = (int) $tputWidth[0];
                }
                if ($returnVar2 === 0 && !empty($tputHeight[0])) {
                    $height = (int) $tputHeight[0];
                }
            }
        }

        // Method 3: Environment variables as fallback
        if ($width === 80 && isset($_ENV['COLUMNS'])) {
            $width = (int) $_ENV['COLUMNS'];
        }
        if ($height === 24 && isset($_ENV['LINES'])) {
            $height = (int) $_ENV['LINES'];
        }

        return new self($width, $height);
    }

    public static function current(): self
    {
        if (self::$instance === null) {
            self::$instance = self::detect();
        }
        return self::$instance;
    }

    public static function refresh(): self
    {
        self::$instance = self::detect();
        return self::$instance;
    }

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

    public function __toString(): string
    {
        return "{$this->width}x{$this->height}";
    }

    public function equals(Size $other): bool
    {
        return $this->width === $other->getWidth() && 
               $this->height === $other->getHeight();
    }
}