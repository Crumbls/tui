<?php

declare(strict_types=1);

namespace Crumbls\Tui\Components;

use Crumbls\Tui\Components\Concerns\Focusable;
use Crumbls\Tui\Components\Concerns\HasEventHandlers;
use Crumbls\Tui\Components\Contracts\Component;
use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Extension\Core\Widget\Block\Padding;
use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Text\Title;
use Crumbls\Tui\Widget\Borders;
use Crumbls\Tui\Widget\BorderType;

/**
 * Container component that can provide a border, title and padding.
 * 
 * This is the new component-based version of BlockWidget with event handling
 * and focus management capabilities.
 */
final class Block implements Component
{
    use Focusable;
    use HasEventHandlers;

    /**
     * @var Component[]
     */
    protected array $children = [];

    /**
     * @param int-mask-of<Borders::*> $borders
     * @param Title[] $titles
     */
    public function __construct(
        public int $borders = Borders::NONE,
        public array $titles = [],
        public BorderType $borderType = BorderType::Plain,
        public Style $borderStyle = new Style(),
        public Style $style = new Style(),
        public Style $titleStyle = new Style(),
        public Padding $padding = new Padding(0, 0, 0, 0),
        public ?Component $content = null,
    ) {
        if ($this->content) {
            $this->children[] = $this->content;
            $this->content->setParent($this);
        }
    }

    /**
     * Create a new Block component.
     */
    public static function make(): self
    {
        return new self();
    }

    /**
     * Create a bordered block.
     */
    public static function bordered(): self
    {
        return new self(borders: Borders::ALL);
    }

    /**
     * Create a card-style block (bordered + rounded + padded).
     */
    public static function card(): self
    {
        return new self(
            borders: Borders::ALL,
            borderType: BorderType::Rounded,
            padding: Padding::uniform(1)
        );
    }

    /**
     * Set the content component.
     */
    public function content(Component $content): self
    {
        if ($this->content) {
            $this->content->setParent(null);
        }

        $this->content = $content;
        $content->setParent($this);
        $this->children = [$content];

        return $this;
    }

    /**
     * Set border configuration.
     * 
     * @param int-mask-of<Borders::*> $flag
     */
    public function borders(int $flag): self
    {
        $this->borders = $flag;
        return $this;
    }

    /**
     * Add titles to this block.
     */
    public function titles(Title ...$titles): self
    {
        $this->titles = $titles;
        return $this;
    }

    /**
     * Add a single title.
     */
    public function title(string|Title $title): self
    {
        $titleObj = $title instanceof Title ? $title : Title::fromString($title);
        $this->titles = [$titleObj];
        return $this;
    }

    /**
     * Set border type.
     */
    public function borderType(BorderType $borderType): self
    {
        $this->borderType = $borderType;
        return $this;
    }

    /**
     * Make borders rounded.
     */
    public function rounded(): self
    {
        $this->borderType = BorderType::Rounded;
        return $this;
    }

    /**
     * Set the main style.
     */
    public function style(Style $style): self
    {
        $this->style = $style;
        return $this;
    }

    /**
     * Set border style.
     */
    public function borderStyle(Style $style): self
    {
        $this->borderStyle = $style;
        return $this;
    }

    /**
     * Set title style.
     */
    public function titleStyle(Style $style): self
    {
        $this->titleStyle = $style;
        return $this;
    }

    /**
     * Set padding.
     */
    public function padding(Padding $padding): self
    {
        $this->padding = $padding;
        return $this;
    }

    /**
     * Set uniform padding.
     */
    public function padded(int $padding = 1): self
    {
        $this->padding = Padding::uniform($padding);
        return $this;
    }

    /**
     * Calculate the inner area after borders and padding.
     */
    public function inner(Area $area): Area
    {
        $x = $area->position->x;
        $y = $area->position->y;
        $width = $area->width;
        $height = $area->height;

        if ($this->borders & Borders::LEFT) {
            $x = min($x + 1, $area->right());
            $width = max(0, $width - 1);
        }
        if ($this->borders & Borders::TOP || [] !== $this->titles) {
            $y = min($y + 1, $area->bottom());
            $height = max(0, $height - 1);
        }
        if ($this->borders & Borders::RIGHT) {
            $width = max(0, $width - 1);
        }
        if ($this->borders & Borders::BOTTOM) {
            $height = max(0, $height - 1);
        }

        $x += $this->padding->left;
        $y += $this->padding->top;
        $width = max(0, $width - ($this->padding->left + $this->padding->right));
        $height = max(0, $height - ($this->padding->top + $this->padding->bottom));

        return Area::fromScalars($x, $y, $width, $height);
    }

    /**
     * Get child components.
     * 
     * @return Component[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }
}