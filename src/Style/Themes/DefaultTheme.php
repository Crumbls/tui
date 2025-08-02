<?php

declare(strict_types=1);

namespace Crumbls\Tui\Style\Themes;

use Crumbls\Tui\Style\AbstractTheme;

class DefaultTheme extends AbstractTheme
{
    public function getName(): string
    {
        return 'default';
    }

    public function getDescription(): string
    {
        return 'Clean professional theme with blue accents and white backgrounds';
    }

    protected function getColors(): array
    {
        return [
            // Focus indicators
            'focus_indicator' => self::BRIGHT_CYAN . self::BOLD,
            'focus_border' => self::BRIGHT_BLUE,
            
            // Tab styling
            'tab_active_focused' => self::BRIGHT_WHITE . self::BOLD,     // Bright white + bold
            'tab_active_unfocused' => self::BRIGHT_WHITE,                // Bright white
            'tab_inactive' => self::BRIGHT_BLACK,                        // Dim gray
            
            // Table styling  
            'table_selected_focused' => self::BG_BLUE . self::BRIGHT_WHITE . self::BOLD,  // Blue bg + white text + bold
            'table_selected_unfocused' => self::BG_BLACK . self::BRIGHT_WHITE,            // Black bg + white text
            'table_header' => self::BRIGHT_WHITE . self::BOLD,                            // Bright white + bold
            'table_border' => self::BRIGHT_BLUE,                                          // Blue borders
            'table_pagination' => self::BRIGHT_CYAN,                                      // Cyan pagination info
            
            // General UI
            'success' => self::BRIGHT_GREEN,
            'warning' => self::BRIGHT_YELLOW,
            'error' => self::BRIGHT_RED,
            'info' => self::BRIGHT_CYAN,
            'muted' => self::BRIGHT_BLACK,
        ];
    }
}