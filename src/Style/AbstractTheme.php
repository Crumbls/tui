<?php

declare(strict_types=1);

namespace Crumbls\Tui\Style;

use Crumbls\Tui\Contracts\ThemeInterface;

abstract class AbstractTheme implements ThemeInterface
{
    public const RESET = "\033[0m";
    
    // ANSI color codes
    public const BLACK = "\033[30m";
    public const RED = "\033[31m";
    public const GREEN = "\033[32m";
    public const YELLOW = "\033[33m";
    public const BLUE = "\033[34m";
    public const MAGENTA = "\033[35m";
    public const CYAN = "\033[36m";
    public const WHITE = "\033[37m";
    
    // Bright colors
    public const BRIGHT_BLACK = "\033[90m";
    public const BRIGHT_RED = "\033[91m";
    public const BRIGHT_GREEN = "\033[92m";
    public const BRIGHT_YELLOW = "\033[93m";
    public const BRIGHT_BLUE = "\033[94m";
    public const BRIGHT_MAGENTA = "\033[95m";
    public const BRIGHT_CYAN = "\033[96m";
    public const BRIGHT_WHITE = "\033[97m";
    
    // Background colors
    public const BG_BLACK = "\033[40m";
    public const BG_RED = "\033[41m";
    public const BG_GREEN = "\033[42m";
    public const BG_YELLOW = "\033[43m";
    public const BG_BLUE = "\033[44m";
    public const BG_MAGENTA = "\033[45m";
    public const BG_CYAN = "\033[46m";
    public const BG_WHITE = "\033[47m";
    
    // Styles
    public const BOLD = "\033[1m";
    public const DIM = "\033[2m";
    public const UNDERLINE = "\033[4m";
    public const REVERSE = "\033[7m";

    /**
     * Theme color configuration - to be defined by concrete themes.
     */
    abstract protected function getColors(): array;

    public function getColor(string $key): string
    {
        return $this->getColors()[$key] ?? '';
    }

    public function hasColor(string $key): bool
    {
        return array_key_exists($key, $this->getColors());
    }

    public function getAvailableKeys(): array
    {
        return array_keys($this->getColors());
    }

    public function apply(string $colorKey, string $content): string
    {
        $color = $this->getColor($colorKey);
        if (empty($color)) {
            return $content;
        }
        
        return $color . $content . self::RESET;
    }

    public function getConfiguration(): array
    {
        return $this->getColors();
    }
}