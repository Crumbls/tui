<?php

declare(strict_types=1);

namespace Crumbls\Tui\Style\Themes;

use Crumbls\Tui\Style\AbstractTheme;

/**
 * Example custom theme showing how to create corporate-branded themes.
 */
class CorporateTheme extends AbstractTheme
{
    public function getName(): string
    {
        return 'corporate';
    }

    public function getDescription(): string
    {
        return 'Professional corporate theme with subtle colors and high readability';
    }

    protected function getColors(): array
    {
        return [
            // Focus indicators - Subtle blue corporate color
            'focus_indicator' => self::BLUE . self::BOLD,
            'focus_border' => self::BLUE,
            
            // Tab styling - Clean corporate look
            'tab_active_focused' => self::BG_WHITE . self::BLUE . self::BOLD,   // Blue text on white
            'tab_active_unfocused' => self::BG_WHITE . self::BLUE,             // Blue text on white
            'tab_inactive' => self::DIM . self::WHITE,                         // Dim white
            
            // Table styling - Professional data presentation
            'table_selected_focused' => self::BG_BLUE . self::WHITE . self::BOLD,     // Blue bg + white text
            'table_selected_unfocused' => self::UNDERLINE . self::WHITE,              // Underlined white
            'table_header' => self::WHITE . self::BOLD . self::UNDERLINE,             // Bold underlined headers
            'table_border' => self::BLUE,                                             // Blue borders
            'table_pagination' => self::BLUE,                                         // Blue pagination
            
            // General UI - Corporate color scheme
            'success' => self::GREEN . self::BOLD,     // Success green
            'warning' => self::YELLOW . self::BOLD,    // Warning amber
            'error' => self::RED . self::BOLD,         // Error red
            'info' => self::BLUE,                      // Info blue
            'muted' => self::BRIGHT_BLACK,             // Muted gray
        ];
    }
}