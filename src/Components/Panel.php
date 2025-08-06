<?php

namespace Crumbls\Tui\Components;

use Crumbls\Tui\Components\Concerns\HasBorders;

class Panel extends Component
{
    use HasBorders;

    public static function make(string $title = ''): self
    {
        $panel = new self();
        $panel->initializeBorders();
        return $panel->title($title);
    }

    public function position(int $x, int $y): self
    {
        $this->x = $x;
        $this->y = $y;
        return $this;
    }
    
    /**
     * Get available width for child components (account for borders)
     */
    public function getAvailableWidth(): int
    {
        return $this->getContentWidth($this->getWidth());
    }
    
    /**
     * Get available height per child component (account for borders and explicit sizes)
     */
    public function getAvailableHeightPerChild(): int
    {
        $contentHeight = $this->getContentHeight($this->getHeight());
        $children = $this->children();
        
        if ($children->isEmpty()) {
            return $contentHeight;
        }
        
        // Calculate space used by children with explicit heights
        $usedHeight = 0;
        $autoSizeCount = 0;
        
        foreach ($children as $child) {
            if (property_exists($child, 'explicitHeight') && $child->explicitHeight) {
                $usedHeight += $child->height;
            } else {
                $autoSizeCount++;
            }
        }
        
        // Distribute remaining space among auto-sizing children
        if ($autoSizeCount === 0) {
            return 0;
        }
        
        $remainingHeight = max(0, $contentHeight - $usedHeight);
        return (int) floor($remainingHeight / $autoSizeCount);
    }

    public function render(): string
    {
        $title = $this->getTitle();
        $width = $this->getWidth();
        $height = $this->getHeight();
        $contentWidth = $this->getContentWidth($width);
        $contentHeight = $this->getContentHeight($height);
        $contentLines = [];

        // Track auto-inserted title lines for height calculation
        $autoTitleLineCount = 0;
        
        // Auto-insert title as paragraph if noBorder() was called and title exists
        if ($this->shouldAutoInsertTitle()) {
            $titleLines = explode("\n", $this->getTitle());
            foreach ($titleLines as $line) {
                if (mb_strlen($line) > $contentWidth) {
                    $contentLines[] = mb_substr($line, 0, $contentWidth);
                } else {
                    $contentLines[] = $line;
                }
                $autoTitleLineCount++;
            }
            
            // Add empty line after title if there are children
            if ($this->children()->isNotEmpty()) {
                $contentLines[] = '';
                $autoTitleLineCount++;
            }
        }

        // Render children content and constrain to fit within content area
        if ($this->children()->isNotEmpty()) {
            foreach ($this->children() as $child) {
                $childOutput = $child->render();
                $childLines = explode("\n", $childOutput);
                
                // Constrain each line to fit within content width
                foreach ($childLines as $line) {
                    if (mb_strlen($line) > $contentWidth) {
                        $contentLines[] = mb_substr($line, 0, $contentWidth);
                    } else {
                        $contentLines[] = $line;
                    }
                }
            }
        }

        // Pad content lines to fill the full content height
        // The contentHeight already accounts for the total space available
        while (count($contentLines) < $contentHeight) {
            $contentLines[] = '';
        }

        // Trim content lines if they exceed content height
        if (count($contentLines) > $contentHeight) {
            $contentLines = array_slice($contentLines, 0, $contentHeight);
        }

        return $this->renderBorderedBox($width, $height, $contentLines, $title);
    }
}