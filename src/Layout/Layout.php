<?php

declare(strict_types=1);

namespace Crumbls\Tui\Layout;

use Crumbls\Tui\Contracts\WidgetInterface;
use Crumbls\Tui\Layout\Margin;

/**
 * Core layout class for constraint-based, nestable TUI layouts.
 * Supports vertical/horizontal direction, constraints, and recursive rendering.
 *
 * - Each child is a WidgetInterface or Layout.
 * - On render, divides available space among children (even split for now; constraints in future).
 * - Supports deep nesting for sidebar/main/content, etc.
 */
final class Layout implements WidgetInterface
{
    public string $direction;
    public Margin $margin;
    public array $constraints;
    public bool $expandToFill;
    private array $children = [];
    private ?int $regionWidth = null;
    private ?int $regionHeight = null;

    private function __construct(
        string $direction,
        Margin $margin,
        array $constraints,
        bool $expandToFill,
        array $children = []
    ) {
        $this->direction = $direction;
        $this->margin = $margin;
        $this->constraints = $constraints;
        $this->expandToFill = $expandToFill;
        $this->children = $children;
    }

    /**
     * Creates a new Layout instance with default settings.
     *
     * @return self
     */
    public static function default(): self
    {
        return new self(
            'vertical',
            Margin::none(),
            [],
            true
        );
    }

    /**
     * Create a vertical layout with children.
     * @param array $children
     * @return static
     */
    public static function vertical(array $children = []): static
    {
        $layout = static::default();
        $layout->direction = 'vertical';
        $layout->children = $children;
        return $layout;
    }

    /**
     * Create a horizontal layout with children.
     * @param array $children
     * @return static
     */
    public static function horizontal(array $children = []): static
    {
        $layout = static::default();
        $layout->direction = 'horizontal';
        $layout->children = $children;
        return $layout;
    }

    public function add(WidgetInterface $widget, $constraint = null): self
    {
        $this->children[] = $widget;
        $this->constraints[] = $constraint;
        return $this;
    }

    public function setRegion(int $width, int $height): static
    {
        $this->regionWidth = $width;
        $this->regionHeight = $height;
        // Propagate to children
        $count = count($this->children);
        if ($count > 0) {
            if ($this->direction === 'vertical') {
                $regionHeight = intdiv($height, $count);
                foreach ($this->children as $child) {
                    if (method_exists($child, 'setRegion')) {
                        $child->setRegion($width, $regionHeight);
                    }
                }
            } else {
                $regionWidth = intdiv($width, $count);
                foreach ($this->children as $child) {
                    if (method_exists($child, 'setRegion')) {
                        $child->setRegion($regionWidth, $height);
                    }
                }
            }
        }
        return $this;
    }

    public function render(): string
    {
        $width = $this->regionWidth ?? 80;
        $height = $this->regionHeight ?? 24;
        $output = '';
        $count = count($this->children);
        if ($count === 0) return $output;

        if ($this->direction === 'vertical') {
            $regionHeight = intdiv($height, $count);
            foreach ($this->children as $i => $child) {
                // Pass the full width and allocated height to each child
                if (method_exists($child, 'setRegion')) {
                    $child->setRegion($width, $regionHeight);
                }
                $output .= $child->render();
                if ($i < $count - 1) {
                    $output .= "\n"; // Add newline between children
                }
            }
        } else {
            $regionWidth = intdiv($width, $count);
            $childLines = [];
            foreach ($this->children as $i => $child) {
                // Pass the allocated width and full height to each child
                if (method_exists($child, 'setRegion')) {
                    $child->setRegion($regionWidth, $height);
                }
                $lines = explode("\n", $child->render());
                $childLines[$i] = $lines;
            }
            for ($line = 0; $line < $height; $line++) {
                foreach ($childLines as $lines) {
                    $output .= ($lines[$line] ?? str_repeat(' ', $regionWidth));
                }
                $output .= "\n";
            }
        }
        return $output;
    }

    public function toArray(): array
    {
        return [
            'type' => 'layout',
            'direction' => $this->direction,
            'children' => array_map(fn($child) => method_exists($child, 'toArray') ? $child->toArray() : (string)$child, $this->children),
            'constraints' => $this->constraints,
        ];
    }

    public static function make(...$args): static
    {
        return new static(...$args);
    }
}
