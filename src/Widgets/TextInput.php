<?php

declare(strict_types=1);

namespace Crumbls\Tui\Widgets;

use Crumbls\Tui\Concerns\HasFocus;
use Crumbls\Tui\Contracts\FocusableInterface;
use Crumbls\Tui\Style\ColorTheme;
use Crumbls\Tui\Widget;

class TextInput extends Widget implements FocusableInterface
{
    use HasFocus;
    
    protected string $value = '';
    protected string $placeholder = '';
    protected ?string $label = null;
    protected int $width = 30;
    protected int $maxLength = 255;
    protected int $cursorPosition = 0;
    protected bool $password = false;
    protected array $validators = [];
    protected ?string $error = null;

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function width(int $width): static
    {
        $this->width = max(5, $width);
        return $this;
    }

    public function maxLength(int $length): static
    {
        $this->maxLength = max(1, $length);
        return $this;
    }

    public function password(bool $password = true): static
    {
        $this->password = $password;
        return $this;
    }

    public function value(string $value): static
    {
        $this->value = substr($value, 0, $this->maxLength);
        $this->cursorPosition = min(strlen($this->value), $this->cursorPosition);
        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function validate(callable $validator): static
    {
        $this->validators[] = $validator instanceof \Closure ? $validator : \Closure::fromCallable($validator);
        return $this;
    }

    public function setRegion(int $width, int $height): static
    {
        $this->width = min($width - 4, $this->width); // Leave some margin
        return $this;
    }

    public function handleKey(string $key): bool
    {
        if (!$this->hasFocus()) {
            return false;
        }

        $handled = match ($key) {
            "\x7f", "\x08" => $this->handleBackspace(), // Backspace/Delete
            "\033[C" => $this->moveCursorRight(), // Right arrow
            "\033[D" => $this->moveCursorLeft(), // Left arrow
            "\033[H" => $this->moveCursorToStart(), // Home
            "\033[F" => $this->moveCursorToEnd(), // End
            "\x15" => $this->clearInput(), // Ctrl+U (clear line)
            default => $this->handleCharacterInput($key),
        };

        if ($handled) {
            $this->validateInput();
        }

        return $handled;
    }

    protected function handleBackspace(): bool
    {
        if ($this->cursorPosition > 0) {
            $this->value = substr($this->value, 0, $this->cursorPosition - 1) 
                         . substr($this->value, $this->cursorPosition);
            $this->cursorPosition--;
            return true;
        }
        return false;
    }

    protected function moveCursorRight(): bool
    {
        if ($this->cursorPosition < strlen($this->value)) {
            $this->cursorPosition++;
            return true;
        }
        return false;
    }

    protected function moveCursorLeft(): bool
    {
        if ($this->cursorPosition > 0) {
            $this->cursorPosition--;
            return true;
        }
        return false;
    }

    protected function moveCursorToStart(): bool
    {
        if ($this->cursorPosition > 0) {
            $this->cursorPosition = 0;
            return true;
        }
        return false;
    }

    protected function moveCursorToEnd(): bool
    {
        $endPos = strlen($this->value);
        if ($this->cursorPosition < $endPos) {
            $this->cursorPosition = $endPos;
            return true;
        }
        return false;
    }

    protected function clearInput(): bool
    {
        if (!empty($this->value)) {
            $this->value = '';
            $this->cursorPosition = 0;
            return true;
        }
        return false;
    }

    protected function handleCharacterInput(string $key): bool
    {
        // Filter out control characters and escape sequences
        if (strlen($key) === 1 && ord($key) >= 32 && ord($key) <= 126) {
            if (strlen($this->value) < $this->maxLength) {
                $this->value = substr($this->value, 0, $this->cursorPosition) 
                             . $key 
                             . substr($this->value, $this->cursorPosition);
                $this->cursorPosition++;
                return true;
            }
        }
        return false;
    }

    protected function validateInput(): void
    {
        $this->error = null;
        foreach ($this->validators as $validator) {
            $result = $validator($this->value);
            if ($result !== true) {
                $this->error = is_string($result) ? $result : 'Invalid input';
                break;
            }
        }
    }

    public function render(): string
    {
        $output = '';

        // Render label if provided
        if ($this->label) {
            $labelColor = $this->error ? 'error' : ($this->hasFocus() ? 'focus_indicator' : 'muted');
            $output .= ColorTheme::apply($labelColor, $this->label) . "\n";
        }

        // Render input box
        $displayValue = $this->password ? str_repeat('*', strlen($this->value)) : $this->value;
        $placeholder = empty($this->value) ? $this->placeholder : '';
        
        // Calculate visible content within the input width
        $availableWidth = $this->width - 2; // Account for borders
        $content = $displayValue ?: $placeholder;
        
        // Handle scrolling if content is longer than available width
        $visibleStart = 0;
        if ($this->cursorPosition >= $availableWidth) {
            $visibleStart = $this->cursorPosition - $availableWidth + 1;
        }
        $visibleContent = substr($content, $visibleStart, $availableWidth);
        
        // Calculate cursor position within visible area
        $visibleCursorPos = $this->cursorPosition - $visibleStart;
        
        // Pad content to fill the input width
        $paddedContent = str_pad($visibleContent, $availableWidth, ' ');
        
        // Apply styling based on state
        if ($this->error) {
            $inputStyle = 'error';
            $borderChar = '─';
        } elseif ($this->hasFocus()) {
            $inputStyle = 'focus_indicator';
            $borderChar = '═';
        } else {
            $inputStyle = 'muted';
            $borderChar = '─';
        }

        // Render the input box
        $topBorder = '┌' . str_repeat($borderChar, $this->width - 2) . '┐';
        $bottomBorder = '└' . str_repeat($borderChar, $this->width - 2) . '┘';
        
        $output .= ColorTheme::apply($inputStyle, $topBorder) . "\n";
        
        // Render content with cursor if focused
        if ($this->hasFocus() && !empty($this->value)) {
            // Insert cursor marker at the right position
            $beforeCursor = substr($paddedContent, 0, $visibleCursorPos);
            $atCursor = substr($paddedContent, $visibleCursorPos, 1);
            $afterCursor = substr($paddedContent, $visibleCursorPos + 1);
            $contentWithCursor = $beforeCursor . ColorTheme::apply('table_selected_focused', $atCursor) . $afterCursor;
            $output .= ColorTheme::apply($inputStyle, '│') . $contentWithCursor . ColorTheme::apply($inputStyle, '│') . "\n";
        } else {
            // No cursor or placeholder styling
            $contentColor = empty($this->value) ? 'muted' : 'info';
            $styledContent = ColorTheme::apply($contentColor, $paddedContent);
            $output .= ColorTheme::apply($inputStyle, '│') . $styledContent . ColorTheme::apply($inputStyle, '│') . "\n";
        }
        
        $output .= ColorTheme::apply($inputStyle, $bottomBorder) . "\n";

        // Render error message if present
        if ($this->error) {
            $output .= ColorTheme::apply('error', '⚠ ' . $this->error) . "\n";
        }

        // Add help text if focused
        if ($this->hasFocus()) {
            $helpText = 'Type to edit • ←→:move cursor • Ctrl+U:clear • Backspace:delete';
            $output .= ColorTheme::apply('muted', $helpText) . "\n";
        }

        return $output;
    }

    public function getFocusableChildren(): array
    {
        return [];
    }
}