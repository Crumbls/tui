<?php

declare(strict_types=1);

namespace Crumbls\Tui\Table\Columns;

use Crumbls\Tui\Style\ThemeManager;
use Crumbls\Tui\Table\Column;

class BadgeColumn extends Column
{
    protected array $colors = [];
    protected array $icons = [];

    public function colors(array $colors): static
    {
        $this->colors = $colors;
        return $this;
    }

    public function color(string $color, string|array $condition): static
    {
        if (is_array($condition)) {
            foreach ($condition as $value) {
                $this->colors[$color] = $value;
            }
        } else {
            $this->colors[$color] = $condition;
        }
        return $this;
    }

    public function icons(array $icons): static
    {
        $this->icons = $icons;
        return $this;
    }

    protected function formatForDisplay(mixed $value, array $record): string
    {
        if ($value === null) {
            return '';
        }

        $text = (string) $value;
        $color = $this->getColorForValue($value);
        $icon = $this->getIconForValue($value);

        // Add icon if defined
        if ($icon) {
            $text = $icon . ' ' . $text;
        }

        // Apply color if defined
        if ($color) {
            $colorKey = match($color) {
                'success' => 'success',
                'warning' => 'warning', 
                'error' => 'error',
                'info' => 'info',
                default => 'info'
            };
            $text = ThemeManager::apply($colorKey, $text);
        }

        return $text;
    }

    protected function getColorForValue(mixed $value): ?string
    {
        foreach ($this->colors as $color => $condition) {
            if (is_array($condition)) {
                if (in_array($value, $condition)) {
                    return $color;
                }
            } elseif ($condition === $value) {
                return $color;
            }
        }
        return null;
    }

    protected function getIconForValue(mixed $value): ?string
    {
        return $this->icons[$value] ?? null;
    }
}