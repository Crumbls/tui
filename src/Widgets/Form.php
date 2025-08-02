<?php

declare(strict_types=1);

namespace Crumbls\Tui\Widgets;

use Crumbls\Tui\Concerns\HasFocus;
use Crumbls\Tui\Contracts\FocusableInterface;
use Crumbls\Tui\Style\ColorTheme;
use Crumbls\Tui\Widget;

class Form extends Widget implements FocusableInterface
{
    use HasFocus;
    
    protected array $fields = [];
    protected int $currentFieldIndex = 0;
    protected ?string $title = null;
    protected int $spacing = 1;

    public function title(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function spacing(int $lines): static
    {
        $this->spacing = max(0, $lines);
        return $this;
    }

    public function addField(FocusableInterface $field): static
    {
        $this->fields[] = $field;
        return $this;
    }

    public function fields(array $fields): static
    {
        $this->fields = array_filter($fields, fn($field) => $field instanceof FocusableInterface);
        return $this;
    }

    public function getField(int $index): ?FocusableInterface
    {
        return $this->fields[$index] ?? null;
    }

    public function getCurrentField(): ?FocusableInterface
    {
        return $this->getField($this->currentFieldIndex);
    }

    public function setRegion(int $width, int $height): static
    {
        // Pass region to all fields
        foreach ($this->fields as $field) {
            if (method_exists($field, 'setRegion')) {
                $field->setRegion($width, 5); // Give each field some reasonable height
            }
        }
        return $this;
    }

    public function handleKey(string $key): bool
    {
        if (!$this->hasFocus() || empty($this->fields)) {
            return false;
        }

        $currentField = $this->getCurrentField();

        // First, try to let the current field handle the key
        if ($currentField && $currentField->handleKey($key)) {
            return true;
        }

        // Handle form navigation
        return match ($key) {
            "\t" => $this->focusNextField(), // Tab to next field
            "\033[Z" => $this->focusPrevField(), // Shift+Tab to previous field
            "\033[B", 'j' => $this->focusNextField(), // Down arrow or 'j'
            "\033[A", 'k' => $this->focusPrevField(), // Up arrow or 'k'
            default => false,
        };
    }

    protected function focusNextField(): bool
    {
        if (empty($this->fields)) {
            return false;
        }

        $this->getCurrentField()?->setFocus(false);
        $this->currentFieldIndex = ($this->currentFieldIndex + 1) % count($this->fields);
        $this->getCurrentField()?->setFocus(true);
        return true;
    }

    protected function focusPrevField(): bool
    {
        if (empty($this->fields)) {
            return false;
        }

        $this->getCurrentField()?->setFocus(false);
        $this->currentFieldIndex = ($this->currentFieldIndex - 1 + count($this->fields)) % count($this->fields);
        $this->getCurrentField()?->setFocus(true);
        return true;
    }

    public function setFocus(bool $focus): void
    {
        $this->hasFocus = $focus;
        
        if ($focus && !empty($this->fields)) {
            // Focus the current field when form gets focus
            $this->getCurrentField()?->setFocus(true);
        } else {
            // Unfocus all fields when form loses focus
            foreach ($this->fields as $field) {
                $field->setFocus(false);
            }
        }
    }

    public function render(): string
    {
        $output = '';

        // Render title if provided
        if ($this->title) {
            $titleColor = $this->hasFocus() ? 'focus_indicator' : 'info';
            $output .= ColorTheme::apply($titleColor, $this->title) . "\n";
            for ($i = 0; $i < $this->spacing; $i++) {
                $output .= "\n";
            }
        }

        // Render each field with spacing
        foreach ($this->fields as $index => $field) {
            $output .= $field->render();
            
            // Add spacing between fields (except after the last one)
            if ($index < count($this->fields) - 1) {
                for ($i = 0; $i < $this->spacing; $i++) {
                    $output .= "\n";
                }
            }
        }

        // Add help text if focused
        if ($this->hasFocus()) {
            $output .= "\n" . ColorTheme::apply('muted', 'Tab/↓:next field • Shift+Tab/↑:prev field') . "\n";
        }

        return $output;
    }

    public function getFocusableChildren(): array
    {
        return $this->fields;
    }

    public function getValues(): array
    {
        $values = [];
        foreach ($this->fields as $index => $field) {
            if (method_exists($field, 'getValue')) {
                $values[$index] = $field->getValue();
            }
        }
        return $values;
    }
}