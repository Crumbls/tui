<?php

namespace Crumbls\Tui\Components;

class Row extends Component
{
    protected int $spacing = 1; // Default spacing between columns

    public static function make(): self
    {
        return new self();
    }

    public function spacing(int $spacing): self
    {
        $this->spacing = $spacing;
        return $this;
    }

    /**
     * Get available width for each child component in horizontal layout
     */
    public function getAvailableWidth(): int
    {
        $childCount = $this->children()->count();
        if ($childCount === 0) {
            return $this->getWidth();
        }

        // Calculate space used by spacing between components
        $spacingUsed = $this->spacing * ($childCount - 1);
        $availableWidth = $this->getWidth() - $spacingUsed;
        
        return (int) floor($availableWidth / $childCount);
    }

    /**
     * Get available height per child component (all children get full height in row layout)
     */
    public function getAvailableHeightPerChild(): int
    {
        return $this->getHeight();
    }

    public function render(): string
    {
        if ($this->children()->isEmpty()) {
            return '';
        }

        $children = $this->children();
        $childWidth = $this->getAvailableWidth();
        $height = $this->getHeight();

        // Render each child and split into lines
        $childOutputs = [];
        $maxLines = 0;

        foreach ($children as $child) {
            $childOutput = $child->render();
            $lines = explode("\n", $childOutput);
            
            // Pad/trim each line to exact child width
            $paddedLines = [];
            foreach ($lines as $line) {
                if (mb_strlen($line) > $childWidth) {
                    $paddedLines[] = mb_substr($line, 0, $childWidth);
                } else {
                    $paddedLines[] = $line . str_repeat(' ', $childWidth - mb_strlen($line));
                }
            }
            
            $childOutputs[] = $paddedLines;
            $maxLines = max($maxLines, count($paddedLines));
        }

        // Combine children horizontally, line by line
        $result = [];
        for ($lineIndex = 0; $lineIndex < min($maxLines, $height); $lineIndex++) {
            $combinedLine = '';
            foreach ($childOutputs as $i => $childLines) {
                $line = $childLines[$lineIndex] ?? str_repeat(' ', $childWidth);
                $combinedLine .= $line;
                
                // Add spacing between columns (except after last column)
                if ($i < count($childOutputs) - 1) {
                    $combinedLine .= str_repeat(' ', $this->spacing);
                }
            }
            $result[] = $combinedLine;
        }

        return implode("\n", $result);
    }
}