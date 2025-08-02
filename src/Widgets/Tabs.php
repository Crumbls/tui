<?php

declare(strict_types=1);

namespace Crumbls\Tui\Widgets;

use Crumbls\Tui\Concerns\HasFocus;
use Crumbls\Tui\Concerns\RendersFocusBorder;
use Crumbls\Tui\Contracts\FocusableInterface;
use Crumbls\Tui\Contracts\WidgetInterface;
use Crumbls\Tui\Style\ColorTheme;
use Crumbls\Tui\Style\FocusStyle;
use Crumbls\Tui\Widgets\Tab;

class Tabs implements WidgetInterface, FocusableInterface
{
    use HasFocus, RendersFocusBorder;
    
    protected array $tabs;
    protected int $selected;
    protected ?int $width = null;
    protected ?string $shortcut = null;

    public function __construct(array $tabs, int $selected = 0)
    {
        $this->tabs = $tabs;
        $this->selected = $selected;
    }

    // Comply with WidgetInterface:make()
    public static function make(): static
    {
        return new static([], 0);
    }

    // For ergonomic usage
    public static function from(array $tabs, int $selected = 0): static
    {
        $instances = array_map(function ($tab) {
            if ($tab instanceof Tab) return $tab;
            if (is_array($tab)) {
                $t = Tab::make($tab['label'] ?? $tab[0] ?? '', $tab['content'] ?? null);
                if (isset($tab['shortcut'])) $t->shortcut($tab['shortcut']);
                return $t;
            }
            return Tab::make((string)$tab, null);
        }, $tabs);
        $instance = new static([], $selected);
        $instance->tabs = $instances;
        $instance->selected = $selected;
        return $instance;
    }

    public function setSelected(int $index): static
    {
        $this->selected = $index;
        return $this;
    }

    public function setRegion(int $width, int $height): static
    {
        $this->width = $width;
        return $this;
    }

    public function shortcut(string $key): static
    {
        $this->shortcut = $key;
        return $this;
    }

    public function canHaveShortcut(): bool
    {
        return true;
    }

    public function getShortcut(): ?string
    {
        return $this->shortcut;
    }

    public function render(): string
    {
        $width = $this->width ?? 80;
        $tabCount = count($this->tabs);
        if ($tabCount === 0) return '';
        $tabWidth = intdiv($width, $tabCount);
        $out = '';
        foreach ($this->tabs as $i => $tab) {
            if ($tab instanceof Tab) {
                $label = ' ' . $tab->getLabel();
                $shortcut = $tab->getShortcut();
                if ($shortcut) {
                    $shortcutLabel = $shortcut;
                    $ord = ord($shortcut);
                    if ($ord >= 1 && $ord <= 26) {
                        // Try to use the actual Ctrl symbol if available, else fallback to 'Ctrl+'
                        $ctrl = function_exists('mb_chr') ? (mb_chr(0x2303, 'UTF-8') . '+' . chr(ord('A') + $ord - 1)) : ('Ctrl+' . chr(ord('A') + $ord - 1));
                        $shortcutLabel = $ctrl;
                    }
                    $label .= ' (' . $shortcutLabel . ')';
                }
                $label .= ' ';
            } else {
                $label = ' ' . (string)$tab . ' ';
            }
            $label = str_pad($label, $tabWidth, ' ', STR_PAD_BOTH);
            if ($i === $this->selected) {
                if ($this->hasFocus()) {
                    $label = ColorTheme::apply('tab_active_focused', $label);
                } else {
                    $label = ColorTheme::apply('tab_active_unfocused', $label);
                }
            } else {
                $label = ColorTheme::apply('tab_inactive', $label);
            }
            $out .= $label;
        }
        // Add focus indicator if focused
        if ($this->hasFocus()) {
            $focusIndicator = FocusStyle::renderFocusLabel('Tab Navigation', true);
            return $focusIndicator . "\n" . $out;
        }
        
        return $out;
    }

    public function toArray(): array
    {
        return [
            'type' => 'tabs',
            'tabs' => $this->tabs,
            'selected' => $this->selected,
            'width' => $this->width,
        ];
    }

    public function getSelected(): int
    {
        return $this->selected;
    }

    public function getTabs(): array
    {
        return $this->tabs;
    }

    public function handleKey(string $key): bool
    {
        if (!$this->hasFocus()) {
            return false;
        }

        $handled = match ($key) {
            "\033[C", 'l' => $this->moveToNextTab(), // Right arrow or 'l'
            "\033[D", 'h' => $this->moveToPrevTab(), // Left arrow or 'h'
            default => $this->handleTabShortcuts($key),
        };

        return $handled;
    }

    protected function moveToNextTab(): bool
    {
        $count = count($this->tabs);
        if ($count <= 1) return false;

        // Find next navigable tab
        for ($i = 1; $i < $count; $i++) {
            $next = ($this->selected + $i) % $count;
            $tab = $this->tabs[$next];
            if ($tab instanceof Tab && $tab->isNavigable()) {
                $this->selected = $next;
                return true;
            }
        }
        return false;
    }

    protected function moveToPrevTab(): bool
    {
        $count = count($this->tabs);
        if ($count <= 1) return false;

        // Find previous navigable tab
        for ($i = 1; $i < $count; $i++) {
            $prev = ($this->selected - $i + $count) % $count;
            $tab = $this->tabs[$prev];
            if ($tab instanceof Tab && $tab->isNavigable()) {
                $this->selected = $prev;
                return true;
            }
        }
        return false;
    }

    protected function handleTabShortcuts(string $key): bool
    {
        foreach ($this->tabs as $i => $tab) {
            if ($tab instanceof Tab && $tab->getShortcut() && strtolower($key) === strtolower($tab->getShortcut())) {
                $this->selected = $i;
                return true;
            }
        }
        return false;
    }

    public function getFocusableChildren(): array
    {
        $currentTab = $this->tabs[$this->selected] ?? null;
        if ($currentTab instanceof Tab) {
            $content = $currentTab->getContent();
            if ($content instanceof FocusableInterface) {
                return [$content];
            }
        }
        return [];
    }
}