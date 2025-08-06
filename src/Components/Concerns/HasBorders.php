<?php

namespace Crumbls\Tui\Components\Concerns;

use Crumbls\Tui\Components\BorderStyle;

trait HasBorders
{
    protected BorderStyle $borderStyle;
    protected bool $showBorders = true;
    protected bool $showTitle = true;
    protected bool $autoInsertTitle = false;

    /**
     * Initialize border settings with default single style
     */
    protected function initializeBorders(): void
    {
        $this->borderStyle = BorderStyle::single();
    }

    /**
     * Set the border style
     */
    public function borderStyle(BorderStyle $style): self
    {
        $this->borderStyle = $style;
        return $this;
    }

    /**
     * Convenience methods for common border styles
     */
    public function singleBorder(): self
    {
        return $this->borderStyle(BorderStyle::single());
    }

    public function doubleBorder(): self
    {
        return $this->borderStyle(BorderStyle::double());
    }

    public function roundedBorder(): self
    {
        return $this->borderStyle(BorderStyle::rounded());
    }

    public function thickBorder(): self
    {
        return $this->borderStyle(BorderStyle::thick());
    }

    public function asciiBorder(): self
    {
        return $this->borderStyle(BorderStyle::ascii());
    }

    public function dottedBorder(): self
    {
        return $this->borderStyle(BorderStyle::dotted());
    }

    public function focusedBorder(): self
    {
        return $this->borderStyle(BorderStyle::focused());
    }

    /**
     * Set border color
     */
    public function borderColor(string $color): self
    {
        $this->borderStyle = $this->getBorderStyle()->color($color);
        return $this;
    }

    /**
     * Set focus border color
     */
    public function focusBorderColor(string $color): self
    {
        $this->borderStyle = $this->getBorderStyle()->focusColor($color);
        return $this;
    }

    public function noBorder(): self
    {
        $this->showBorders = false;
        $this->autoInsertTitle = true; // Automatically insert title as paragraph when no borders
        return $this;
    }

    public function noTitle(): self
    {
        $this->showTitle = false;
        $this->autoInsertTitle = false; // Disable auto-insert when explicitly no title wanted
        return $this;
    }

    public function withBorder(bool $show = true): self
    {
        $this->showBorders = $show;
        return $this;
    }

    public function withTitle(bool $show = true): self
    {
        $this->showTitle = $show;
        return $this;
    }

    /**
     * Get the current border style
     */
    protected function getBorderStyle(): BorderStyle
    {
        return $this->borderStyle ?? BorderStyle::single();
    }

    /**
     * Check if borders should be shown
     */
    protected function shouldShowBorders(): bool
    {
        return $this->showBorders;
    }

    /**
     * Check if title should be shown
     */
    protected function shouldShowTitle(): bool
    {
        return $this->showTitle;
    }

    /**
     * Check if title should be auto-inserted as a paragraph
     */
    protected function shouldAutoInsertTitle(): bool
    {
        return $this->autoInsertTitle && !empty($this->getTitle());
    }

    /**
     * Check if component is currently focused (for border coloring)
     */
    protected function isFocused(): bool
    {
        if ($this instanceof \Crumbls\Tui\Contracts\SelectableInterface) {
            return $this->isSelected();
        }
        return false;
    }

    /**
     * Render the top border with optional title
     */
    protected function renderTopBorder(int $width, string $title = ''): string
    {
        if (!$this->shouldShowBorders()) {
            return '';
        }

        $style = $this->getBorderStyle();
        $focused = $this->isFocused();
        
        if (!empty($title) && $this->shouldShowTitle()) {
            $titleText = " {$title} ";
            $titleLength = mb_strlen($titleText);
            // Width = LeftBorder(1) + Horizontal(1) + Title + RemainingHorizontal + RightBorder(1)
            $remainingWidth = max(0, $width - $titleLength - 3); // -3 for left border, first horizontal, right border
            
            return $style->getColored(BorderStyle::TOP_LEFT, $focused) . 
                   $style->getColored(BorderStyle::HORIZONTAL, $focused) . 
                   $titleText . 
                   str_repeat($style->getColored(BorderStyle::HORIZONTAL, $focused), $remainingWidth) . 
                   $style->getColored(BorderStyle::TOP_RIGHT, $focused);
        } else {
            return $style->getColored(BorderStyle::TOP_LEFT, $focused) . 
                   str_repeat($style->getColored(BorderStyle::HORIZONTAL, $focused), $width - 2) . 
                   $style->getColored(BorderStyle::TOP_RIGHT, $focused);
        }
    }

    /**
     * Render the bottom border
     */
    protected function renderBottomBorder(int $width): string
    {
        if (!$this->shouldShowBorders()) {
            return '';
        }

        $style = $this->getBorderStyle();
        $focused = $this->isFocused();
        return $style->getColored(BorderStyle::BOTTOM_LEFT, $focused) . 
               str_repeat($style->getColored(BorderStyle::HORIZONTAL, $focused), $width - 2) . 
               $style->getColored(BorderStyle::BOTTOM_RIGHT, $focused);
    }

    /**
     * Render a content line with side borders
     */
    protected function renderContentLine(string $content, int $width): string
    {
        if (!$this->shouldShowBorders()) {
            return $this->padOrTrimContent($content, $width);
        }

        $style = $this->getBorderStyle();
        $focused = $this->isFocused();
        $contentWidth = $width - 2; // Account for left and right borders
        $paddedContent = $this->padOrTrimContent($content, $contentWidth);
        
        return $style->getColored(BorderStyle::VERTICAL, $focused) . $paddedContent . $style->getColored(BorderStyle::VERTICAL, $focused);
    }

    /**
     * Render an empty content line (just borders)
     */
    protected function renderEmptyLine(int $width): string
    {
        if (!$this->shouldShowBorders()) {
            return str_repeat(' ', $width);
        }

        $style = $this->getBorderStyle();
        $focused = $this->isFocused();
        return $style->getColored(BorderStyle::VERTICAL, $focused) . 
               str_repeat(' ', $width - 2) . 
               $style->getColored(BorderStyle::VERTICAL, $focused);
    }

    /**
     * Calculate effective content width (accounting for borders)
     */
    protected function getContentWidth(int $totalWidth): int
    {
        return $this->shouldShowBorders() ? $totalWidth - 2 : $totalWidth;
    }

    /**
     * Calculate effective content height (accounting for borders)
     */
    protected function getContentHeight(int $totalHeight): int
    {
        return $this->shouldShowBorders() ? $totalHeight - 2 : $totalHeight;
    }

    /**
     * Pad or trim content to fit exact width
     */
    protected function padOrTrimContent(string $content, int $width): string
    {
        $contentLength = mb_strlen($content);
        
        if ($contentLength > $width) {
            return mb_substr($content, 0, $width);
        } else {
            return $content . str_repeat(' ', $width - $contentLength);
        }
    }

    /**
     * Render a complete bordered box with content
     */
    protected function renderBorderedBox(int $width, int $height, array $contentLines = [], string $title = ''): string
    {
        $output = [];

        // Top border
        if ($this->shouldShowBorders()) {
            $output[] = $this->renderTopBorder($width, $title);
        }

        // Content area
        $contentHeight = $this->getContentHeight($height);
        $contentWidth = $this->getContentWidth($width);

        for ($i = 0; $i < $contentHeight; $i++) {
            $line = $contentLines[$i] ?? '';
            $output[] = $this->renderContentLine($line, $width);
        }

        // Bottom border
        if ($this->shouldShowBorders()) {
            $output[] = $this->renderBottomBorder($width);
        }

        return implode("\n", $output);
    }
}