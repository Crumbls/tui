<?php

declare(strict_types=1);

namespace Crumbls\Tui\Widgets;

use Crumbls\Tui\Concerns\HasFocus;
use Crumbls\Tui\Contracts\FocusableInterface;
use Crumbls\Tui\Style\ColorTheme;
use Crumbls\Tui\Widget;

class Select extends Widget implements FocusableInterface
{
    use HasFocus;
    
    protected array $options = [];
    protected mixed $selectedValue = null;
    protected int $selectedIndex = 0;
    protected ?string $label = null;
    protected string $placeholder = 'Select an option...';
    protected int $width = 30;
    protected bool $expanded = false;
    protected int $maxVisibleOptions = 5;

    public function options(array $options): static
    {
        $this->options = $options;
        $this->selectedIndex = 0;
        $this->selectedValue = null;
        return $this;
    }

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function width(int $width): static
    {
        $this->width = max(10, $width);
        return $this;
    }

    public function selected(mixed $value): static
    {
        $index = array_search($value, array_values($this->options));
        if ($index !== false) {
            $this->selectedIndex = $index;
            $this->selectedValue = $value;
        }
        return $this;
    }

    public function selectedByKey(string $key): static
    {
        if (array_key_exists($key, $this->options)) {
            $this->selectedValue = $this->options[$key];
            $this->selectedIndex = array_search($key, array_keys($this->options));
        }
        return $this;
    }

    public function getValue(): mixed
    {
        return $this->selectedValue;
    }

    public function getSelectedKey(): string|int|null
    {
        if ($this->selectedValue === null) {
            return null;
        }
        
        $keys = array_keys($this->options);
        return $keys[$this->selectedIndex] ?? null;
    }

    public function maxVisibleOptions(int $max): static
    {
        $this->maxVisibleOptions = max(3, $max);
        return $this;
    }

    public function setRegion(int $width, int $height): static
    {
        $this->width = min($width - 4, $this->width);
        return $this;
    }

    public function handleKey(string $key): bool
    {
        if (!$this->hasFocus()) {
            return false;
        }

        return match ($key) {
            "\n", "\r", ' ' => $this->toggleExpanded(), // Enter/Space to toggle
            "\033[A", 'k' => $this->moveUp(), // Up arrow or 'k'
            "\033[B", 'j' => $this->moveDown(), // Down arrow or 'j'
            "\x1b" => $this->collapse(), // Escape to close
            'g' => $this->goToFirst(),
            'G' => $this->goToLast(),
            default => $this->handleCharacterSelection($key),
        };
    }

    protected function toggleExpanded(): bool
    {
        $this->expanded = !$this->expanded;
        if (!$this->expanded && $this->selectedIndex < count($this->options)) {
            // Confirm selection
            $values = array_values($this->options);
            $this->selectedValue = $values[$this->selectedIndex] ?? null;
        }
        return true;
    }

    protected function collapse(): bool
    {
        if ($this->expanded) {
            $this->expanded = false;
            return true;
        }
        return false;
    }

    protected function moveUp(): bool
    {
        if ($this->expanded && $this->selectedIndex > 0) {
            $this->selectedIndex--;
            return true;
        }
        return false;
    }

    protected function moveDown(): bool
    {
        if ($this->expanded && $this->selectedIndex < count($this->options) - 1) {
            $this->selectedIndex++;
            return true;
        }
        return false;
    }

    protected function goToFirst(): bool
    {
        if ($this->expanded && $this->selectedIndex > 0) {
            $this->selectedIndex = 0;
            return true;
        }
        return false;
    }

    protected function goToLast(): bool
    {
        $lastIndex = count($this->options) - 1;
        if ($this->expanded && $this->selectedIndex < $lastIndex) {
            $this->selectedIndex = $lastIndex;
            return true;
        }
        return false;
    }

    protected function handleCharacterSelection(string $key): bool
    {
        if (!$this->expanded || strlen($key) !== 1) {
            return false;
        }

        // Find first option that starts with the typed character
        $char = strtolower($key);
        foreach ($this->options as $index => $option) {
            $optionText = is_string($option) ? $option : (string) $option;
            if (strtolower(substr($optionText, 0, 1)) === $char) {
                $this->selectedIndex = array_search($index, array_keys($this->options));
                return true;
            }
        }

        return false;
    }

    public function render(): string
    {
        $output = '';

        // Render label if provided
        if ($this->label) {
            $labelColor = $this->hasFocus() ? 'focus_indicator' : 'muted';
            $output .= ColorTheme::apply($labelColor, $this->label) . "\n";
        }

        // Determine current display value
        $displayValue = $this->selectedValue ? (string) $this->selectedValue : $this->placeholder;
        $displayValue = substr($displayValue, 0, $this->width - 4); // Leave room for arrow and borders

        // Style based on state
        $style = $this->hasFocus() ? 'focus_indicator' : 'muted';
        $borderChar = $this->hasFocus() ? '═' : '─';
        
        // Render the select box
        $topBorder = '┌' . str_repeat($borderChar, $this->width - 2) . '┐';
        $bottomBorder = '└' . str_repeat($borderChar, $this->width - 2) . '┘';
        
        $output .= ColorTheme::apply($style, $topBorder) . "\n";

        // Render the current selection with dropdown arrow
        $arrow = $this->expanded ? '▲' : '▼';
        $contentWidth = $this->width - 4; // Space for borders and arrow
        $paddedValue = str_pad($displayValue, $contentWidth, ' ');
        $valueColor = $this->selectedValue ? 'info' : 'muted';
        
        $output .= ColorTheme::apply($style, '│') 
                 . ColorTheme::apply($valueColor, $paddedValue) 
                 . ColorTheme::apply($style, $arrow . '│') . "\n";

        // Render dropdown options if expanded
        if ($this->expanded && !empty($this->options)) {
            $output .= $this->renderDropdownOptions($style);
        } else {
            $output .= ColorTheme::apply($style, $bottomBorder) . "\n";
        }

        // Add help text if focused
        if ($this->hasFocus()) {
            if ($this->expanded) {
                $helpText = '↑↓:navigate • Enter:select • Esc:close • g/G:first/last • Type:jump';
            } else {
                $helpText = 'Enter/Space:open dropdown';
            }
            $output .= ColorTheme::apply('muted', $helpText) . "\n";
        }

        return $output;
    }

    protected function renderDropdownOptions(string $style): string
    {
        $output = '';
        $optionValues = array_values($this->options);
        $totalOptions = count($optionValues);
        
        // Calculate visible range for scrolling
        $startIndex = 0;
        $endIndex = min($totalOptions, $this->maxVisibleOptions);
        
        if ($totalOptions > $this->maxVisibleOptions) {
            // Calculate scroll position to keep selected item visible
            if ($this->selectedIndex >= $this->maxVisibleOptions) {
                $startIndex = min($this->selectedIndex - $this->maxVisibleOptions + 1, $totalOptions - $this->maxVisibleOptions);
                $endIndex = $startIndex + $this->maxVisibleOptions;
            }
        }

        // Render visible options
        for ($i = $startIndex; $i < $endIndex; $i++) {
            $option = $optionValues[$i];
            $optionText = is_string($option) ? $option : (string) $option;
            $optionText = substr($optionText, 0, $this->width - 4); // Fit within borders
            
            $isSelected = ($i === $this->selectedIndex);
            $paddedOption = str_pad($optionText, $this->width - 4, ' ');
            
            if ($isSelected) {
                $optionContent = ColorTheme::apply('table_selected_focused', '▶' . substr($paddedOption, 1));
            } else {
                $optionContent = ColorTheme::apply('info', ' ' . substr($paddedOption, 1));
            }
            
            $output .= ColorTheme::apply($style, '│') . $optionContent . ColorTheme::apply($style, '│') . "\n";
        }

        // Show scroll indicators if needed
        if ($totalOptions > $this->maxVisibleOptions) {
            $scrollInfo = '(' . ($this->selectedIndex + 1) . '/' . $totalOptions . ')';
            $scrollPadding = str_pad($scrollInfo, $this->width - 2, ' ', STR_PAD_BOTH);
            $output .= ColorTheme::apply('muted', '│' . $scrollPadding . '│') . "\n";
        }

        // Bottom border
        $bottomBorder = '└' . str_repeat('─', $this->width - 2) . '┘';
        $output .= ColorTheme::apply($style, $bottomBorder) . "\n";

        return $output;
    }

    public function getFocusableChildren(): array
    {
        return [];
    }
}