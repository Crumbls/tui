<?php

namespace Crumbls\Tui\Components;

class BorderStyle
{
    // Border character positions
    public const TOP_LEFT = 'top_left';
    public const TOP_RIGHT = 'top_right';
    public const BOTTOM_LEFT = 'bottom_left';
    public const BOTTOM_RIGHT = 'bottom_right';
    public const HORIZONTAL = 'horizontal';
    public const VERTICAL = 'vertical';
    public const TOP_TEE = 'top_tee';
    public const BOTTOM_TEE = 'bottom_tee';
    public const LEFT_TEE = 'left_tee';
    public const RIGHT_TEE = 'right_tee';
    public const CROSS = 'cross';

    protected array $characters;
    protected string $name;
    protected ?string $color = null;
    protected ?string $focusColor = null;

    public function __construct(string $name = 'custom', array $characters = [])
    {
        $this->name = $name;
        // Start with single border defaults
        $this->characters = array_merge([
            self::TOP_LEFT => '┌',
            self::TOP_RIGHT => '┐',
            self::BOTTOM_LEFT => '└',
            self::BOTTOM_RIGHT => '┘',
            self::HORIZONTAL => '─',
            self::VERTICAL => '│',
            self::TOP_TEE => '┬',
            self::BOTTOM_TEE => '┴',
            self::LEFT_TEE => '├',
            self::RIGHT_TEE => '┤',
            self::CROSS => '┼',
        ], $characters);
    }

    public function get(string $position): string
    {
        return $this->characters[$position] ?? '?';
    }

    /**
     * Get a border character with color applied
     */
    public function getColored(string $position, bool $isFocused = false): string
    {
        $char = $this->get($position);
        $color = $this->getColor($isFocused);
        
        if ($color) {
            return $this->applyColor($char, $color);
        }
        
        return $char;
    }

    /**
     * Apply ANSI color code to a character
     */
    protected function applyColor(string $char, string $color): string
    {
        // Only apply color if output supports it (check if we're in a proper terminal)
        if (!$this->supportsColor()) {
            return $char;
        }

        // Handle common color names and ANSI codes
        $colorCodes = [
            'black' => '30',
            'red' => '31',
            'green' => '32',
            'yellow' => '33',
            'blue' => '34',
            'magenta' => '35',
            'cyan' => '36',
            'white' => '37',
            'bright_black' => '90',
            'bright_red' => '91',
            'bright_green' => '92',
            'bright_yellow' => '93',
            'bright_blue' => '94',
            'bright_magenta' => '95',
            'bright_cyan' => '96',
            'bright_white' => '97',
        ];

        $code = $colorCodes[$color] ?? $color;
        return "\033[{$code}m{$char}\033[0m";
    }

    /**
     * Check if the current environment supports color output
     */
    protected function supportsColor(): bool
    {
        // Simple check - only apply colors if we have a proper TTY
        return function_exists('posix_isatty') && @posix_isatty(STDOUT);
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the border color
     */
    public function color(string $color): self
    {
        $this->color = $color;
        return $this;
    }

    /**
     * Set the focus border color (when component is focused/selected)
     */
    public function focusColor(string $color): self
    {
        $this->focusColor = $color;
        return $this;
    }

    /**
     * Get the border color (with focus override)
     */
    public function getColor(bool $isFocused = false): ?string
    {
        if ($isFocused && $this->focusColor) {
            return $this->focusColor;
        }
        return $this->color;
    }

    // Fluent setters for individual characters
    public function topLeft(string $char): self
    {
        $this->characters[self::TOP_LEFT] = $char;
        return $this;
    }

    public function topRight(string $char): self
    {
        $this->characters[self::TOP_RIGHT] = $char;
        return $this;
    }

    public function bottomLeft(string $char): self
    {
        $this->characters[self::BOTTOM_LEFT] = $char;
        return $this;
    }

    public function bottomRight(string $char): self
    {
        $this->characters[self::BOTTOM_RIGHT] = $char;
        return $this;
    }

    public function horizontal(string $char): self
    {
        $this->characters[self::HORIZONTAL] = $char;
        return $this;
    }

    public function vertical(string $char): self
    {
        $this->characters[self::VERTICAL] = $char;
        return $this;
    }

    public function topTee(string $char): self
    {
        $this->characters[self::TOP_TEE] = $char;
        return $this;
    }

    public function bottomTee(string $char): self
    {
        $this->characters[self::BOTTOM_TEE] = $char;
        return $this;
    }

    public function leftTee(string $char): self
    {
        $this->characters[self::LEFT_TEE] = $char;
        return $this;
    }

    public function rightTee(string $char): self
    {
        $this->characters[self::RIGHT_TEE] = $char;
        return $this;
    }

    public function cross(string $char): self
    {
        $this->characters[self::CROSS] = $char;
        return $this;
    }

    // Convenience methods to set multiple characters
    public function corners(string $topLeft, string $topRight, string $bottomLeft, string $bottomRight): self
    {
        return $this->topLeft($topLeft)
                   ->topRight($topRight)
                   ->bottomLeft($bottomLeft)
                   ->bottomRight($bottomRight);
    }

    public function sides(string $horizontal, string $vertical): self
    {
        return $this->horizontal($horizontal)->vertical($vertical);
    }

    public function intersections(string $topTee, string $bottomTee, string $leftTee, string $rightTee, string $cross = null): self
    {
        $style = $this->topTee($topTee)
                     ->bottomTee($bottomTee)
                     ->leftTee($leftTee)
                     ->rightTee($rightTee);
        
        if ($cross !== null) {
            $style->cross($cross);
        }
        
        return $style;
    }

    // Predefined border styles as starting points
    public static function single(): self
    {
        return new self('single'); // Uses defaults which are already single style
    }

    public static function double(): self
    {
        return new self('double')
            ->corners('╔', '╗', '╚', '╝')
            ->sides('═', '║')
            ->intersections('╦', '╩', '╠', '╣', '╬');
    }

    public static function rounded(): self
    {
        return new self('rounded')
            ->corners('╭', '╮', '╰', '╯');
            // Keep default sides and intersections
    }

    public static function focused(): self
    {
        return new self('focused')
            ->color('bright_cyan')
            ->focusColor('bright_yellow');
    }

    public static function thick(): self
    {
        return new self('thick')
            ->corners('┏', '┓', '┗', '┛')
            ->sides('━', '┃')
            ->intersections('┳', '┻', '┣', '┫', '╋');
    }

    public static function ascii(): self
    {
        return new self('ascii')
            ->corners('+', '+', '+', '+')
            ->sides('-', '|')
            ->intersections('+', '+', '+', '+', '+');
    }

    public static function dotted(): self
    {
        return new self('dotted')
            ->sides('┄', '┆');
            // Keep default corners and intersections
    }

    public static function none(): self
    {
        return new self('none')
            ->corners(' ', ' ', ' ', ' ')
            ->sides(' ', ' ')
            ->intersections(' ', ' ', ' ', ' ', ' ');
    }

    // Factory method for completely custom border
    public static function custom(string $name = 'custom'): self
    {
        return new self($name);
    }

    // Get all available preset styles
    public static function presets(): array
    {
        return [
            'single' => self::single(),
            'double' => self::double(),
            'rounded' => self::rounded(),
            'thick' => self::thick(),
            'ascii' => self::ascii(),
            'dotted' => self::dotted(),
            'none' => self::none(),
        ];
    }
}