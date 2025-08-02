<?php

declare(strict_types=1);

namespace Crumbls\Tui\Style;

use Crumbls\Tui\Contracts\ThemeInterface;
use Crumbls\Tui\Style\Themes\DefaultTheme;
use InvalidArgumentException;

class ThemeManager
{
    protected static ?ThemeInterface $currentTheme = null;
    protected static array $registeredThemes = [];

    /**
     * Register a theme.
     */
    public static function register(ThemeInterface $theme): void
    {
        self::$registeredThemes[$theme->getName()] = $theme;
    }

    /**
     * Set the active theme.
     */
    public static function setTheme(ThemeInterface|string $theme): void
    {
        if (is_string($theme)) {
            if (!isset(self::$registeredThemes[$theme])) {
                throw new InvalidArgumentException("Theme '{$theme}' is not registered.");
            }
            $theme = self::$registeredThemes[$theme];
        }

        self::$currentTheme = $theme;
    }

    /**
     * Get the current theme, defaulting to DefaultTheme.
     */
    public static function getCurrentTheme(): ThemeInterface
    {
        if (self::$currentTheme === null) {
            self::$currentTheme = new DefaultTheme();
        }

        return self::$currentTheme;
    }

    /**
     * Get a color from the current theme.
     */
    public static function getColor(string $key): string
    {
        return self::getCurrentTheme()->getColor($key);
    }

    /**
     * Apply a color from the current theme.
     */
    public static function apply(string $colorKey, string $content): string
    {
        return self::getCurrentTheme()->apply($colorKey, $content);
    }

    /**
     * Get all registered themes.
     */
    public static function getRegisteredThemes(): array
    {
        return self::$registeredThemes;
    }

    /**
     * Check if a theme is registered.
     */
    public static function hasTheme(string $name): bool
    {
        return isset(self::$registeredThemes[$name]);
    }

    /**
     * Register default themes.
     */
    public static function registerDefaultThemes(): void
    {
        self::register(new DefaultTheme());
        self::register(new \Crumbls\Tui\Style\Themes\DarkTheme());
        self::register(new \Crumbls\Tui\Style\Themes\OceanTheme());
        
        // Register forest theme if you want to create it
        // self::register(new \Crumbls\Tui\Style\Themes\ForestTheme());
    }
}