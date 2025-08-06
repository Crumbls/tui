<?php

namespace Crumbls\Tui\Components;

use Crumbls\Tui\Events\MouseEvent;
use Crumbls\Tui\Contracts\SelectableInterface;
use Crumbls\Tui\Components\Concerns\Selectable;

class Tabs extends Component implements SelectableInterface
{
    use Selectable;
    
    protected int $activeTab = 0;
    protected array $onTabChange = [];
    protected array $tabs = [];

    public static function make(): self
    {
        $tabs = new static();
        $tabs->selectable(true); // Make tabs selectable by default
        return $tabs;
    }

    /**
     * Set the active tab index
     */
    public function activeTab(int $index): self
    {
        $this->activeTab = $index;
        return $this;
    }

    /**
     * Get the current active tab index
     */
    public function getActiveTab(): int
    {
        return $this->activeTab;
    }

    /**
     * Add a tab change event handler
     */
    public function onTabChange(callable $handler): self
    {
        $this->onTabChange[] = $handler;
        return $this;
    }

    /**
     * Switch to the next tab
     */
    public function nextTab(): self
    {
        $tabCount = count($this->children());
        if ($tabCount > 0) {
            $oldTab = $this->activeTab;
            $this->activeTab = ($this->activeTab + 1) % $tabCount;
            $this->triggerTabChange($oldTab, $this->activeTab);
        }
        return $this;
    }

    /**
     * Switch to the previous tab
     */
    public function previousTab(): self
    {
        $tabCount = count($this->children());
        if ($tabCount > 0) {
            $oldTab = $this->activeTab;
            $this->activeTab = ($this->activeTab - 1 + $tabCount) % $tabCount;
            $this->triggerTabChange($oldTab, $this->activeTab);
        }
        return $this;
    }

    /**
     * Switch to a specific tab by index
     */
    public function switchToTab(int $index): self
    {
        $tabCount = count($this->children());
        if ($index >= 0 && $index < $tabCount && $index !== $this->activeTab) {
            $oldTab = $this->activeTab;
            $this->activeTab = $index;
            $this->triggerTabChange($oldTab, $this->activeTab);
        }
        return $this;
    }

    /**
     * Handle key press events for tab navigation
     */
    public function handleKeyPress(string $key): bool
    {
        return $this->handleKeyInput($key);
    }

    /**
     * Handle key input events for focus system
     */
    public function handleKeyInput(string $key): bool
    {
        // Check for custom key bindings first
        foreach ($this->children() as $index => $tab) {
            if ($tab instanceof Tab && $tab->getKeyBinding()) {
                if ($this->matchesKeyBinding($key, $tab->getKeyBinding())) {
                    $this->switchToTab($index);
                    return true; // Event handled
                }
            }
        }

        // Handle Tab key for next tab
        if ($key === "\t") {
            $this->nextTab();
            return true;
        }

        // Handle Shift+Tab for previous tab
        if ($key === "\033[Z") {
            $this->previousTab();
            return true;
        }

        return false; // Event not handled, let it bubble
    }

    /**
     * Check if a key matches a key binding (e.g., 'ctrl+w', 'cmd+c')
     */
    protected function matchesKeyBinding(string $key, string $binding): bool
    {
        // Parse the binding (e.g., 'ctrl+w' -> ['ctrl', 'w'])
        $parts = explode('+', strtolower($binding));
        if (count($parts) !== 2) {
            return false;
        }

        [$modifier, $letter] = $parts;
        
        // Map key sequences to their modifiers
        $keyMappings = [
            // Ctrl+letters (ASCII control codes)
            'ctrl+a' => "\x01", 'ctrl+b' => "\x02", 'ctrl+c' => "\x03", 'ctrl+d' => "\x04",
            'ctrl+e' => "\x05", 'ctrl+f' => "\x06", 'ctrl+g' => "\x07", 'ctrl+h' => "\x08",
            'ctrl+i' => "\x09", 'ctrl+j' => "\x0A", 'ctrl+k' => "\x0B", 'ctrl+l' => "\x0C",
            'ctrl+m' => "\x0D", 'ctrl+n' => "\x0E", 'ctrl+o' => "\x0F", 'ctrl+p' => "\x10",
            'ctrl+q' => "\x11", 'ctrl+r' => "\x12", 'ctrl+s' => "\x13", 'ctrl+t' => "\x14",
            'ctrl+u' => "\x15", 'ctrl+v' => "\x16", 'ctrl+w' => "\x17", 'ctrl+x' => "\x18",
            'ctrl+y' => "\x19", 'ctrl+z' => "\x1A",
            
            // Ctrl+numbers (safe alternatives to letters)
            'ctrl+1' => "\x1B" . '1',  // ESC + 1 sequence for ctrl+1
            'ctrl+2' => "\x1B" . '2',  // ESC + 2 sequence for ctrl+2  
            'ctrl+3' => "\x1B" . '3',  // ESC + 3 sequence for ctrl+3
            'ctrl+4' => "\x1B" . '4',  // ESC + 4 sequence for ctrl+4
            'ctrl+5' => "\x1B" . '5',  // ESC + 5 sequence for ctrl+5
            'ctrl+6' => "\x1B" . '6',  // ESC + 6 sequence for ctrl+6
            'ctrl+7' => "\x1B" . '7',  // ESC + 7 sequence for ctrl+7
            'ctrl+8' => "\x1B" . '8',  // ESC + 8 sequence for ctrl+8
            'ctrl+9' => "\x1B" . '9',  // ESC + 9 sequence for ctrl+9
            'ctrl+0' => "\x1B" . '0',  // ESC + 0 sequence for ctrl+0
            
            // Note: cmd+letter and alt+letter are harder to detect in terminal
            // For now, focus on ctrl combinations which are standard
        ];

        $expectedKey = $keyMappings[$binding] ?? null;
        return $expectedKey && $key === $expectedKey;
    }

    /**
     * Handle mouse click events on tabs
     */
    public function handleMouseClick(MouseEvent $event): bool
    {
        return $this->handleMouseInput($event);
    }
    
    /**
     * Handle mouse input events for focus system
     */
    public function handleMouseInput(MouseEvent $event): bool
    {
        if ($event->isClick() && $event->clickedComponent) {
            // Check if click was on a tab child
            foreach ($this->children() as $index => $child) {
                if ($child->getId() === $event->clickedComponent->getId()) {
                    $this->switchToTab($index);
                    return true; // Event handled
                }
            }
        }

        return false; // Event not handled, let it bubble
    }

    /**
     * Trigger tab change handlers
     */
    protected function triggerTabChange(int $oldTab, int $newTab): void
    {
        foreach ($this->onTabChange as $handler) {
            $handler($oldTab, $newTab, $this);
        }
    }

    /**
     * Add a tab component
     */
    public function addTab(Tab $tab): self
    {
        return $this->with($tab);
    }

    /**
     * Create tabs from an array of labels
     */
    public function tabs(array $labels): self
    {
        foreach ($labels as $index => $label) {
            $tab = Tab::make($label)
                ->active($index === $this->activeTab);
            $this->addTab($tab);
        }
        return $this;
    }

    /**
     * Create tabs with key bindings (e.g., ['Welcome' => 'ctrl+w', 'Components' => 'ctrl+c'])
     */
    public function tabsWithKeys(array $tabsWithKeys): self
    {
        $index = 0;
        foreach ($tabsWithKeys as $label => $keyBinding) {
            $tab = Tab::make($label)
                ->keyBinding($keyBinding)
                ->active($index === $this->activeTab);
            $this->addTab($tab);
            $index++;
        }
        return $this;
    }

    /**
     * Render the tabs component
     */
    public function render(): string
    {
        $tabLabels = [];
        $hasKeyBindings = false;
        
        foreach ($this->children() as $index => $tab) {
            if ($tab instanceof Tab) {
                $marker = $index === $this->activeTab ? '► ' : '  ';
                $label = $tab->getLabel();
                
                // Add key binding if it exists
                if ($tab->getKeyBinding()) {
                    $keyDisplay = strtoupper(str_replace('ctrl+', 'Ctrl+', $tab->getKeyBinding()));
                    $label .= " ({$keyDisplay})";
                    $hasKeyBindings = true;
                }
                
                $tabLabels[] = $marker . $label;
            }
        }

        $tabBar = implode(' | ', $tabLabels);
        
        // Add navigation hint based on whether we have key bindings
        if ($hasKeyBindings) {
            $hint = 'Use keyboard shortcuts or Tab/Shift+Tab to navigate • Press q to quit';
        } else {
            $hint = 'Press Tab/Shift+Tab to navigate tabs • Press q to quit';
        }
        
        return $tabBar . "\n" . $hint;
    }
}