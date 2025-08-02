<?php

declare(strict_types=1);

namespace Crumbls\Tui\Style\Themes;

use Crumbls\Tui\Style\AbstractTheme;

class DarkTheme extends AbstractTheme
{
    public function getName(): string
    {
        return 'dark';
    }

    public function getDescription(): string
    {
        return 'High contrast dark theme with yellow accents';
    }

    protected function getColors(): array
    {
        return [
            'focus_indicator' => self::BRIGHT_YELLOW . self::BOLD,
            'focus_border' => self::BRIGHT_YELLOW,
            'tab_active_focused' => self::BG_WHITE . self::BLACK . self::BOLD,
            'tab_active_unfocused' => self::BG_WHITE . self::BLACK,
            'tab_inactive' => self::BRIGHT_BLACK,
            'table_selected_focused' => self::BG_YELLOW . self::BLACK . self::BOLD,
            'table_selected_unfocused' => self::BG_BLACK . self::BRIGHT_YELLOW,
            'table_header' => self::BRIGHT_YELLOW . self::BOLD,
            'table_border' => self::BRIGHT_YELLOW,
            'table_pagination' => self::BRIGHT_YELLOW,
            'success' => self::BRIGHT_GREEN,
            'warning' => self::BRIGHT_YELLOW,
            'error' => self::BRIGHT_RED,
            'info' => self::BRIGHT_YELLOW,
            'muted' => self::BRIGHT_BLACK,
        ];
    }
}