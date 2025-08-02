<?php

declare(strict_types=1);

namespace Crumbls\Tui\Widgets;

use Crumbls\Tui\Concerns\HasFocus;
use Crumbls\Tui\Contracts\FocusableInterface;
use Crumbls\Tui\Style\ColorTheme;
use Crumbls\Tui\Widget;

class Button extends Widget implements FocusableInterface
{
    use HasFocus;
    
    protected string $text;
    protected ?\Closure $onPress = null;
    protected string $variant = 'primary';
    protected int $minWidth = 10;
    protected bool $disabled = false;

    public function __construct(string $text = 'Button')
    {
        $this->text = $text;
    }

    public static function make(array $attributes = []): static
    {
        $text = $attributes['text'] ?? 'Button';
        return new static($text);
    }

    public static function withText(string $text): static
    {
        return new static($text);
    }

    public function text(string $text): static
    {
        $this->text = $text;
        return $this;
    }

    public function onPress(callable $callback): static
    {
        $this->onPress = $callback instanceof \Closure ? $callback : \Closure::fromCallable($callback);
        return $this;
    }

    public function variant(string $variant): static
    {
        $this->variant = $variant; // primary, secondary, success, warning, error
        return $this;
    }

    public function minWidth(int $width): static
    {
        $this->minWidth = max(3, $width);
        return $this;
    }

    public function disabled(bool $disabled = true): static
    {
        $this->disabled = $disabled;
        return $this;
    }

    public function handleKey(string $key): bool
    {
        if (!$this->hasFocus() || $this->disabled) {
            return false;
        }

        if ($key === "\n" || $key === "\r" || $key === ' ') {
            return $this->press();
        }

        return false;
    }

    protected function press(): bool
    {
        if ($this->onPress && !$this->disabled) {
            try {
                ($this->onPress)($this);
                return true;
            } catch (\Exception $e) {
                // Handle callback errors gracefully
                return false;
            }
        }
        return false;
    }

    public function render(): string
    {
        $buttonWidth = max($this->minWidth, strlen($this->text) + 4); // 2 chars padding on each side
        $paddedText = str_pad($this->text, $buttonWidth - 2, ' ', STR_PAD_BOTH);
        
        // Determine styling based on variant and state
        $colorKey = $this->getColorKey();
        
        // Create button borders
        $topBorder = '┌' . str_repeat('─', $buttonWidth - 2) . '┐';
        $bottomBorder = '└' . str_repeat('─', $buttonWidth - 2) . '┘';
        
        if ($this->disabled) {
            // Disabled styling
            $output = ColorTheme::apply('muted', $topBorder) . "\n";
            $output .= ColorTheme::apply('muted', '│' . $paddedText . '│') . "\n";
            $output .= ColorTheme::apply('muted', $bottomBorder) . "\n";
        } elseif ($this->hasFocus()) {
            // Focused styling with enhanced borders
            $focusTopBorder = '╔' . str_repeat('═', $buttonWidth - 2) . '╗';
            $focusBottomBorder = '╚' . str_repeat('═', $buttonWidth - 2) . '╝';
            
            $output = ColorTheme::apply($colorKey, $focusTopBorder) . "\n";
            $output .= ColorTheme::apply($colorKey, '║' . $paddedText . '║') . "\n";
            $output .= ColorTheme::apply($colorKey, $focusBottomBorder) . "\n";
            
            // Add focus help text
            $output .= ColorTheme::apply('muted', 'Press Enter or Space to activate') . "\n";
        } else {
            // Normal styling
            $output = ColorTheme::apply($colorKey, $topBorder) . "\n";
            $output .= ColorTheme::apply($colorKey, '│' . $paddedText . '│') . "\n";
            $output .= ColorTheme::apply($colorKey, $bottomBorder) . "\n";
        }

        return $output;
    }

    protected function getColorKey(): string
    {
        if ($this->disabled) {
            return 'muted';
        }

        return match ($this->variant) {
            'primary' => $this->hasFocus() ? 'focus_indicator' : 'info',
            'secondary' => $this->hasFocus() ? 'focus_indicator' : 'muted',
            'success' => 'success',
            'warning' => 'warning',
            'error' => 'error',
            default => $this->hasFocus() ? 'focus_indicator' : 'info',
        };
    }

    public function getFocusableChildren(): array
    {
        return [];
    }
}