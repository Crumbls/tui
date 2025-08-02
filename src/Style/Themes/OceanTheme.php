<?php

declare(strict_types=1);

namespace Crumbls\Tui\Style\Themes;

use Crumbls\Tui\Style\AbstractTheme;

class OceanTheme extends AbstractTheme
{
    public function getName(): string
    {
        return 'ocean';
    }

    public function getDescription(): string
    {
        return 'Cool ocean theme with cyan and blue tones';
    }

    protected function getColors(): array
    {
        return [
            'focus_indicator' => self::BRIGHT_CYAN . self::BOLD,
            'focus_border' => self::CYAN,
            'tab_active_focused' => self::BG_CYAN . self::BLACK . self::BOLD,
            'tab_active_unfocused' => self::BG_CYAN . self::BLACK,
            'tab_inactive' => self::DIM . self::CYAN,
            'table_selected_focused' => self::BG_CYAN . self::BLACK . self::BOLD,
            'table_selected_unfocused' => self::BG_BLACK . self::BRIGHT_CYAN,
            'table_header' => self::BRIGHT_CYAN . self::BOLD,
            'table_border' => self::CYAN,
            'table_pagination' => self::BRIGHT_CYAN,
            'success' => self::BRIGHT_GREEN,
            'warning' => self::BRIGHT_YELLOW,
            'error' => self::BRIGHT_RED,
            'info' => self::BRIGHT_CYAN,
            'muted' => self::DIM . self::CYAN,
        ];
    }
}