<?php

declare(strict_types=1);

namespace Crumbls\Tui\Style;

/**
 * Backward compatibility wrapper for the new theme system.
 * @deprecated Use ThemeManager directly instead.
 */
class ColorTheme
{
    /**
     * Get a color from the current theme.
     */
    public static function get(string $key, string $fallback = ''): string
    {
        $color = ThemeManager::getColor($key);
        return $color ?: $fallback;
    }
    
    /**
     * Load a predefined theme.
     */
    public static function loadTheme(string $theme): void
    {
        // Initialize default themes if not already done
        if (!ThemeManager::hasTheme('default')) {
            ThemeManager::registerDefaultThemes();
        }
        
        if (ThemeManager::hasTheme($theme)) {
            ThemeManager::setTheme($theme);
        }
    }
    
    /**
     * Apply color and return reset code.
     */
    public static function apply(string $colorKey, string $content, string $fallback = ''): string
    {
        return ThemeManager::apply($colorKey, $content);
    }
}