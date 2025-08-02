<?php

declare(strict_types=1);

namespace Crumbls\Tui\Style;

class FocusStyle
{
    public const RESET = "\033[0m";
    
    // Unicode box drawing characters
    public const NORMAL_BORDER = [
        'top_left' => '┌',
        'top_right' => '┐', 
        'bottom_left' => '└',
        'bottom_right' => '┘',
        'horizontal' => '─',
        'vertical' => '│',
        'cross' => '┼'
    ];
    
    public const FOCUS_BORDER = [
        'top_left' => '╔',
        'top_right' => '╗',
        'bottom_left' => '╚', 
        'bottom_right' => '╝',
        'horizontal' => '═',
        'vertical' => '║',
        'cross' => '╬'
    ];
    
    public const HEAVY_BORDER = [
        'top_left' => '┏',
        'top_right' => '┓',
        'bottom_left' => '┗',
        'bottom_right' => '┛', 
        'horizontal' => '━',
        'vertical' => '┃',
        'cross' => '╋'
    ];

    public static function getFocusBorder(bool $focused): array
    {
        return $focused ? self::FOCUS_BORDER : self::NORMAL_BORDER;
    }
    
    public static function getFocusColor(bool $focused, bool $selected = false): string
    {
        if ($selected) {
            return $focused 
                ? ColorTheme::get('table_selected_focused') 
                : ColorTheme::get('table_selected_unfocused');
        }
        return $focused 
            ? ColorTheme::get('focus_indicator') 
            : ColorTheme::get('muted');
    }
    
    public static function wrapWithFocus(string $content, bool $focused, bool $selected = false): string
    {
        $color = self::getFocusColor($focused, $selected);
        return $color . $content . self::RESET;
    }
    
    public static function renderFocusLabel(string $label, bool $focused): string
    {
        if (!$focused) {
            return '';
        }
        
        return ColorTheme::apply('focus_indicator', '● ' . strtoupper($label) . ' (ACTIVE)');
    }
}