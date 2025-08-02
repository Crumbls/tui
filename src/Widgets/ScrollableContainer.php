<?php

declare(strict_types=1);

namespace Crumbls\Tui\Widgets;

use Crumbls\Tui\Concerns\HasFocus;
use Crumbls\Tui\Contracts\FocusableInterface;
use Crumbls\Tui\Contracts\WidgetInterface;
use Crumbls\Tui\Style\ColorTheme;
use Crumbls\Tui\Widget;

class ScrollableContainer extends Widget implements FocusableInterface
{
    use HasFocus;
    
    protected WidgetInterface $content;
    protected int $scrollOffset = 0;
    protected int $containerHeight = 20;
    protected int $containerWidth = 80;
    protected int $scrollStep = 1;
    protected bool $showScrollbar = true;

    public function __construct(WidgetInterface $content)
    {
        $this->content = $content;
        parent::__construct();
    }

    public static function make(array $attributes = []): static
    {
        // This won't work with just attributes, so we'll override the factory method
        throw new \InvalidArgumentException('Use ScrollableContainer::wrap($content) instead');
    }

    public static function wrap(WidgetInterface $content): static
    {
        return new static($content);
    }

    public function showScrollbar(bool $show = true): static
    {
        $this->showScrollbar = $show;
        return $this;
    }

    public function setRegion(int $width, int $height): static
    {
        $this->containerWidth = $width;
        $this->containerHeight = max(10, $height - 2); // Reserve 2 lines for help text
        
        // Give the content full width but let it use its natural height
        if (method_exists($this->content, 'setRegion')) {
            $contentWidth = $this->showScrollbar ? $width - 2 : $width; // Leave space for scrollbar
            $this->content->setRegion($contentWidth, 200); // Large height so content can render fully
        }
        
        return $this;
    }

    public function handleKey(string $key): bool
    {
        if (!$this->hasFocus()) {
            return false;
        }

        // First, try to pass the key to the content if it's focusable
        if ($this->content instanceof FocusableInterface && $this->content->hasFocus()) {
            if ($this->content->handleKey($key)) {
                return true;
            }
        }

        // Handle scrolling keys
        return match ($key) {
            "\033[A", 'k' => $this->scrollUp(), // Up arrow or 'k'
            "\033[B", 'j' => $this->scrollDown(), // Down arrow or 'j'
            "\033[5~", 'u' => $this->pageUp(), // Page Up or 'u'
            "\033[6~", 'd' => $this->pageDown(), // Page Down or 'd'
            'g' => $this->scrollToTop(), // Go to top
            'G' => $this->scrollToBottom(), // Go to bottom
            default => false,
        };
    }

    protected function scrollUp(): bool
    {
        if ($this->scrollOffset > 0) {
            $this->scrollOffset = max(0, $this->scrollOffset - $this->scrollStep);
            return true;
        }
        return false;
    }

    protected function scrollDown(): bool
    {
        $maxScroll = $this->getMaxScrollOffset();
        if ($this->scrollOffset < $maxScroll) {
            $this->scrollOffset = min($maxScroll, $this->scrollOffset + $this->scrollStep);
            return true;
        }
        return false;
    }

    protected function pageUp(): bool
    {
        $pageSize = max(1, intval($this->containerHeight * 0.8)); // 80% of container height
        $newOffset = max(0, $this->scrollOffset - $pageSize);
        if ($newOffset !== $this->scrollOffset) {
            $this->scrollOffset = $newOffset;
            return true;
        }
        return false;
    }

    protected function pageDown(): bool
    {
        $maxScroll = $this->getMaxScrollOffset();
        $pageSize = max(1, intval($this->containerHeight * 0.8)); // 80% of container height
        $newOffset = min($maxScroll, $this->scrollOffset + $pageSize);
        if ($newOffset !== $this->scrollOffset) {
            $this->scrollOffset = $newOffset;
            return true;
        }
        return false;
    }

    protected function scrollToTop(): bool
    {
        if ($this->scrollOffset > 0) {
            $this->scrollOffset = 0;
            return true;
        }
        return false;
    }

    protected function scrollToBottom(): bool
    {
        $maxScroll = $this->getMaxScrollOffset();
        if ($this->scrollOffset < $maxScroll) {
            $this->scrollOffset = $maxScroll;
            return true;
        }
        return false;
    }

    protected function getMaxScrollOffset(): int
    {
        $contentLines = $this->getContentLines();
        return max(0, count($contentLines) - $this->containerHeight + 1);
    }

    protected function getContentLines(): array
    {
        $rendered = $this->content->render();
        return explode("\n", $rendered);
    }

    public function render(): string
    {
        $contentLines = $this->getContentLines();
        $totalContentLines = count($contentLines);
        
        // Calculate visible lines
        $visibleHeight = $this->containerHeight;
        $startLine = $this->scrollOffset;
        $endLine = min($startLine + $visibleHeight, $totalContentLines);
        
        $output = '';
        $contentWidth = $this->showScrollbar ? $this->containerWidth - 2 : $this->containerWidth;
        
        // Render visible content lines
        for ($i = $startLine; $i < $endLine; $i++) {
            $line = $contentLines[$i] ?? '';
            
            // Clean and truncate line to fit content width
            $cleanLine = $this->cleanAnsiLine($line);
            $truncatedLine = $this->truncateToWidth($line, $contentWidth);
            $paddedLine = $this->padToWidth($truncatedLine, $contentWidth);
            
            if ($this->showScrollbar) {
                $scrollbar = $this->getScrollbarChar($i - $startLine, $totalContentLines);
                $output .= $paddedLine . ' ' . ColorTheme::apply('muted', $scrollbar) . "\n";
            } else {
                $output .= $paddedLine . "\n";
            }
        }
        
        // Fill remaining space if content is shorter than container
        $renderedLines = $endLine - $startLine;
        for ($i = $renderedLines; $i < $visibleHeight; $i++) {
            $emptyLine = str_repeat(' ', $contentWidth);
            
            if ($this->showScrollbar) {
                $scrollbar = $this->getScrollbarChar($i, $totalContentLines);
                $output .= $emptyLine . ' ' . ColorTheme::apply('muted', $scrollbar) . "\n";
            } else {
                $output .= $emptyLine . "\n";
            }
        }
        
        // Add status and help text if focused
        if ($this->hasFocus()) {
            $statusInfo = $this->getScrollStatus($totalContentLines);
            $helpText = '↑↓/jk:scroll • u/d:page • g/G:top/bottom';
            $output .= ColorTheme::apply('info', $statusInfo) . "\n";
            $output .= ColorTheme::apply('muted', $helpText) . "\n";
        }
        
        return rtrim($output, "\n");
    }

    protected function getScrollbarChar(int $lineIndex, int $totalLines): string
    {
        if ($totalLines <= $this->containerHeight) {
            return ' '; // No scrollbar needed
        }
        
        $maxScroll = $this->getMaxScrollOffset();
        
        // Calculate thumb position and size
        $thumbSize = max(1, intval(($this->containerHeight / $totalLines) * $this->containerHeight));
        $thumbPosition = intval(($this->scrollOffset / max(1, $maxScroll)) * ($this->containerHeight - $thumbSize));
        
        // Determine what to show at this line
        if ($lineIndex >= $thumbPosition && $lineIndex < $thumbPosition + $thumbSize) {
            return '█'; // Thumb
        } elseif ($lineIndex === 0 && $this->scrollOffset > 0) {
            return '▲'; // Can scroll up
        } elseif ($lineIndex === $this->containerHeight - 1 && $this->scrollOffset < $maxScroll) {
            return '▼'; // Can scroll down
        } else {
            return '│'; // Track
        }
    }

    protected function cleanAnsiLine(string $line): string
    {
        return preg_replace('/\033\[[0-9;]*m/', '', $line);
    }

    protected function truncateToWidth(string $line, int $width): string
    {
        $cleanLine = $this->cleanAnsiLine($line);
        if (mb_strlen($cleanLine) <= $width) {
            return $line;
        }
        
        // If the line contains ANSI codes, we need to carefully truncate
        if (strpos($line, "\033[") !== false) {
            $result = '';
            $displayWidth = 0;
            $i = 0;
            $len = strlen($line);

            while ($i < $len && $displayWidth < $width) {
                if ($line[$i] === "\033" && $i + 1 < $len && $line[$i + 1] === '[') {
                    // Found ANSI escape sequence, find the end
                    $j = $i + 2;
                    while ($j < $len && !ctype_alpha($line[$j])) {
                        $j++;
                    }
                    if ($j < $len) {
                        $j++; // Include the final letter
                        $result .= substr($line, $i, $j - $i);
                        $i = $j;
                    } else {
                        break;
                    }
                } else {
                    $result .= $line[$i];
                    $displayWidth++;
                    $i++;
                }
            }
            return $result;
        }
        
        // Simple truncation for text without ANSI codes
        return mb_substr($line, 0, $width);
    }

    protected function padToWidth(string $line, int $width): string
    {
        $cleanLine = $this->cleanAnsiLine($line);
        $currentWidth = mb_strlen($cleanLine);
        $padding = max(0, $width - $currentWidth);
        return $line . str_repeat(' ', $padding);
    }

    protected function getScrollStatus(int $totalLines): string
    {
        if ($totalLines <= $this->containerHeight) {
            return "All content visible";
        }
        
        $startLine = $this->scrollOffset + 1;
        $endLine = min($this->scrollOffset + $this->containerHeight, $totalLines);
        $percentage = intval(($this->scrollOffset / max(1, $totalLines - $this->containerHeight)) * 100);
        
        return "Lines {$startLine}-{$endLine} of {$totalLines} ({$percentage}%)";
    }

    public function getFocusableChildren(): array
    {
        if ($this->content instanceof FocusableInterface) {
            return [$this->content];
        }
        return [];
    }
}