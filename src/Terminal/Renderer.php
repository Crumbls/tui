<?php

namespace Crumbls\Tui\Terminal;

use Crumbls\Tui\Components\Contracts\Component;
use Crumbls\Tui\Contracts\InputBusContract;
use Crumbls\Tui\Contracts\RendererContract;
use Crumbls\Tui\Contracts\ScreenContract;
use Crumbls\Tui\Contracts\TerminalContract;
use Crumbls\Tui\Layout\Layout;
use Crumbls\Tui\Layout\VerticalLayout;

class Renderer implements RendererContract
{
    protected TerminalContract $terminal;
    protected ScreenContract $screen;
    protected ?Layout $layout = null;
    protected ?InputBusContract $inputBus = null;

    public function __construct(TerminalContract $terminal = null)
    {
        $this->terminal = $terminal ?? new Terminal();
        $this->screen = new Screen($this->terminal);
        
        // Default to vertical layout for the entire screen
        $this->layout = new VerticalLayout(0, 0, $this->terminal->getWidth(), $this->terminal->getHeight());
    }

    public function setLayout(Layout $layout): self
    {
        $this->layout = $layout;
        return $this;
    }

    public function getTerminal(): Terminal
    {
        return $this->terminal;
    }

    public function getScreen(): Screen
    {
        return $this->screen;
    }

    public function setInputBus(InputBusContract $inputBus): self
    {
        $this->inputBus = $inputBus;
        return $this;
    }

    // =================== RENDERING ===================

    public function render(Component $rootComponent): self
    {
        // Hide cursor during rendering
        $this->terminal->hideCursor();
        
        // Clear the screen buffer for new frame
        $this->screen->clear();
        
        // Clear InputBus components for new frame
        if ($this->inputBus) {
            $this->inputBus->clearComponents();
        }
        
        // Get all components in render order (nested set traversal)
        $components = [$rootComponent, ...$rootComponent->descendants()->all()];
        
        // Calculate positions for all components
        $this->renderComponents($components, $this->layout);
        
        // Render to terminal (handles differential updates)
        $this->screen->render();
        
        return $this;
    }

    protected function renderComponents(array $components, Layout $layout): void
    {
        // Get component positions from layout
        $positions = $layout->calculate($components);
        
        foreach ($components as $component) {
            $componentId = $component->getId();
            if (!isset($positions[$componentId])) {
                continue;
            }
            
            $pos = $positions[$componentId];
            $this->renderComponent($component, $pos['x'], $pos['y'], $pos['width'], $pos['height']);
            
            // Render children with their own layout if they have one
            $children = $component->children()->all();
            if (!empty($children)) {
                // Create child layout within component bounds
                $childLayout = new VerticalLayout($pos['x'] + 1, $pos['y'] + 1, $pos['width'] - 2, $pos['height'] - 2);
                $this->renderComponents($children, $childLayout);
            }
        }
    }

    protected function renderComponent(Component $component, int $x, int $y, int $width, int $height): void
    {
        // Register component with InputBus for hit testing
        if ($this->inputBus) {
            // TODO: Add z-index support to components, for now use depth as z-index
            $zIndex = $component->getDepth();
            $this->inputBus->registerComponent($component, $x, $y, $width, $height, $zIndex);
        }
        
        // Render the component box
        $this->screen->drawBox($x, $y, $width, $height);
        
        // Get component title/content if it's a Panel
        $title = '';
        if (method_exists($component, 'title')) {
            // Access the title property if it exists
            $reflection = new \ReflectionClass($component);
            if ($reflection->hasProperty('title')) {
                $titleProperty = $reflection->getProperty('title');
                $titleProperty->setAccessible(true);
                $title = $titleProperty->getValue($component);
            }
        }
        
        // If no title, use component ID
        if (empty($title)) {
            $title = $component->getId();
        }
        
        // Render title in the top border if it fits
        if (!empty($title) && strlen($title) <= $width - 6) {
            // Clear the top border area for title
            $titlePadding = max(0, $width - strlen($title) - 4);
            $titleText = "─ {$title} " . str_repeat('─', $titlePadding);
            $this->screen->drawString($x + 1, $y, $titleText);
        }
        
        // Add some content inside the box
        if ($height > 2 && $width > 4) {
            // Show some basic info
            $info = "ID: " . substr($component->getId(), 0, $width - 6);
            $this->screen->drawString($x + 2, $y + 1, $info);
            
            // Show dimensions if there's room
            if ($height > 3) {
                $sizeInfo = "Size: {$width}x{$height}";
                if (strlen($sizeInfo) <= $width - 4) {
                    $this->screen->drawString($x + 2, $y + 2, $sizeInfo);
                }
            }
        }
    }

    // =================== TERMINAL LIFECYCLE ===================

    public function initialize(): self
    {
        $this->terminal
            ->enterRawMode()
            ->enableAlternateScreen()
            ->clear()
            ->hideCursor();
            
        return $this;
    }

    public function cleanup(): self
    {
        $this->terminal
            ->showCursor()
            ->disableAlternateScreen()
            ->exitRawMode();
            
        return $this;
    }
}