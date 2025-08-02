<?php

declare(strict_types=1);

namespace Crumbls\Tui\Rendering;

use Crumbls\Tui\Contracts\RendererInterface;
use Crumbls\Tui\Contracts\TerminalInterface;
use Crumbls\Tui\Contracts\ComponentInterface;
use Crumbls\Tui\Contracts\BufferInterface;
use Crumbls\Tui\Rendering\Buffer;

/**
 * Simple renderer that outputs content directly to terminal.
 */
class SimpleRenderer implements RendererInterface
{
    private bool $dirty = true;
    private int $width = 80;
    private int $height = 24;
    private array $content = [];
    private ?ComponentInterface $rootComponent = null;
    private BufferInterface $buffer;

    public function __construct(
        private ?TerminalInterface $terminal = null
    ) {
        $this->buffer = new Buffer($this->width, $this->height);
    }

    public function render(?ComponentInterface $rootComponent = null): string
    {
        // Use provided component or stored root component
        $component = $rootComponent ?? $this->rootComponent;
        
        $output = '';
        
        // Add header
        $output .= $this->renderHeader();
        
        // If we have a component, render it, otherwise use content lines
        if ($component) {
            $componentOutput = $component->render();
            $lines = explode("\n", $componentOutput);
            foreach ($lines as $line) {
                $output .= $this->padLine($line) . "\n";
            }
            
            // Fill remaining lines
            $remainingLines = max(0, $this->height - count($lines) - 2); // -2 for header/footer
            for ($i = 0; $i < $remainingLines; $i++) {
                $output .= str_repeat(' ', $this->width) . "\n";
            }
        } else {
            // Add content lines
            foreach ($this->content as $line) {
                $output .= $this->padLine($line) . "\n";
            }
            
            // Fill remaining lines
            $remainingLines = max(0, $this->height - count($this->content) - 2); // -2 for header
            for ($i = 0; $i < $remainingLines; $i++) {
                $output .= str_repeat(' ', $this->width) . "\n";
            }
        }
        
        // Add footer
        $output .= $this->renderFooter();
        
        return $output;
    }

    public function setRootComponent(?ComponentInterface $component): static
    {
        $this->rootComponent = $component;
        $this->markDirty();
        return $this;
    }

    public function getRootComponent(): ?ComponentInterface
    {
        return $this->rootComponent;
    }

    public function setSize(int $width, int $height): static
    {
        if ($this->width !== $width || $this->height !== $height) {
            $this->width = $width;
            $this->height = $height;
            $this->buffer->resize($width, $height);
            $this->markDirty();
        }
        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function markDirty(): static
    {
        $this->dirty = true;
        return $this;
    }

    public function isDirty(): bool
    {
        return $this->dirty;
    }

    public function clearDirty(): static
    {
        $this->dirty = false;
        return $this;
    }

    public function getBuffer(): BufferInterface
    {
        return $this->buffer;
    }

    /**
     * Add a line of content to render.
     */
    public function addLine(string $line): static
    {
        $this->content[] = $line;
        $this->markDirty();
        return $this;
    }

    /**
     * Clear all content.
     */
    public function clearContent(): static
    {
        $this->content = [];
        $this->markDirty();
        return $this;
    }

    /**
     * Set all content at once.
     */
    public function setContent(array $lines): static
    {
        $this->content = $lines;
        $this->markDirty();
        return $this;
    }

    private function renderHeader(): string
    {
        $title = ' TUI Demo - Integrated Phase 1 Components ';
        $padding = max(0, $this->width - strlen($title));
        $leftPad = intval($padding / 2);
        $rightPad = $padding - $leftPad;
        
        return str_repeat('=', $leftPad) . $title . str_repeat('=', $rightPad) . "\n";
    }

    private function renderFooter(): string
    {
        $footer = " Press 'q' to quit, any other key for activity ";
        $padding = max(0, $this->width - strlen($footer));
        $leftPad = intval($padding / 2);
        $rightPad = $padding - $leftPad;
        
        return str_repeat('-', $leftPad) . $footer . str_repeat('-', $rightPad);
    }

    private function padLine(string $line): string
    {
        // Truncate if too long, pad if too short
        if (strlen($line) > $this->width) {
            return substr($line, 0, $this->width - 3) . '...';
        }
        return str_pad($line, $this->width);
    }
}