<?php

declare(strict_types=1);

namespace Crumbls\Tui\Focus;

use Crumbls\Tui\Contracts\FocusManagerInterface;
use Crumbls\Tui\Contracts\SelectableInterface;
use Crumbls\Tui\Contracts\PositionableInterface;
use Crumbls\Tui\Component;

/**
 * Manages focus and selection across component trees.
 */
class FocusManager implements FocusManagerInterface
{
    private ?SelectableInterface $focused = null;
    private array $roots = [];
    private array $tabOrder = [];
    private bool $enabled = true;

    public function setFocus(SelectableInterface $component): void
    {
        if (!$this->enabled || !$component->isSelectable()) {
            return;
        }

        // Clear previous focus
        if ($this->focused && $this->focused !== $component) {
            $this->focused->setSelected(false);
        }

        // Set new focus
        $this->focused = $component;
        $component->setSelected(true);
    }

    public function getFocused(): ?SelectableInterface
    {
        return $this->focused;
    }

    public function clearFocus(): void
    {
        if ($this->focused) {
            $this->focused->setSelected(false);
            $this->focused = null;
        }
    }

    public function focusNext(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $selectables = $this->getSelectableComponents();
        if (empty($selectables)) {
            return false;
        }

        // Use tab order if defined
        if (!empty($this->tabOrder)) {
            return $this->focusNextInTabOrder();
        }

        // Find current focus index
        $currentIndex = -1;
        if ($this->focused) {
            foreach ($selectables as $index => $component) {
                if ($component === $this->focused) {
                    $currentIndex = $index;
                    break;
                }
            }
        }

        // Move to next component
        $nextIndex = ($currentIndex + 1) % count($selectables);
        $this->setFocus($selectables[$nextIndex]);
        
        return true;
    }

    public function focusPrevious(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $selectables = $this->getSelectableComponents();
        if (empty($selectables)) {
            return false;
        }

        // Use tab order if defined
        if (!empty($this->tabOrder)) {
            return $this->focusPreviousInTabOrder();
        }

        // Find current focus index
        $currentIndex = 0;
        if ($this->focused) {
            foreach ($selectables as $index => $component) {
                if ($component === $this->focused) {
                    $currentIndex = $index;
                    break;
                }
            }
        }

        // Move to previous component
        $prevIndex = ($currentIndex - 1 + count($selectables)) % count($selectables);
        $this->setFocus($selectables[$prevIndex]);
        
        return true;
    }

    public function registerRoot(PositionableInterface $root): void
    {
        $this->roots[$root instanceof Component ? $root->getId() : spl_object_id($root)] = $root;
    }

    public function unregisterRoot(PositionableInterface $root): void
    {
        $key = $root instanceof Component ? $root->getId() : spl_object_id($root);
        unset($this->roots[$key]);
        
        // Clear focus if the focused component was in this root
        if ($this->focused && $root instanceof Component) {
            $selectables = $root->getSelectableComponents();
            if (in_array($this->focused, $selectables, true)) {
                $this->clearFocus();
            }
        }
    }

    public function getComponentAt(int $x, int $y): ?SelectableInterface
    {
        $candidates = [];

        foreach ($this->roots as $root) {
            if ($root instanceof Component) {
                $component = $root->getComponentAt($x, $y);
                if ($component && $component instanceof SelectableInterface && $component->isSelectable()) {
                    $candidates[] = $component;
                }
            }
        }

        // Return the component with highest selection priority
        if (!empty($candidates)) {
            usort($candidates, fn($a, $b) => $b->getSelectionPriority() <=> $a->getSelectionPriority());
            return $candidates[0];
        }

        return null;
    }

    public function handleMouseClick(int $x, int $y): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $component = $this->getComponentAt($x, $y);
        if ($component && $component->canReceiveMouse()) {
            $this->setFocus($component);
            return true;
        }

        return false;
    }

    public function getSelectableComponents(): array
    {
        $components = [];

        foreach ($this->roots as $root) {
            if ($root instanceof Component) {
                $components = array_merge($components, $root->getSelectableComponents());
            }
        }

        // Sort by selection priority and position
        usort($components, function($a, $b) {
            $priorityDiff = $b->getSelectionPriority() <=> $a->getSelectionPriority();
            if ($priorityDiff !== 0) {
                return $priorityDiff;
            }
            
            // If same priority, sort by position (top-left first)
            if ($a instanceof PositionableInterface && $b instanceof PositionableInterface) {
                $yDiff = $a->getAbsoluteY() <=> $b->getAbsoluteY();
                if ($yDiff !== 0) {
                    return $yDiff;
                }
                return $a->getAbsoluteX() <=> $b->getAbsoluteX();
            }
            
            return 0;
        });

        return $components;
    }

    public function setTabOrder(array $componentIds): void
    {
        $this->tabOrder = $componentIds;
    }

    public function getTabOrder(): array
    {
        return $this->tabOrder;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
        if (!$enabled) {
            $this->clearFocus();
        }
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    private function focusNextInTabOrder(): bool
    {
        $selectables = $this->getSelectableComponentsById();
        $currentIndex = -1;

        if ($this->focused && $this->focused instanceof Component) {
            $currentIndex = array_search($this->focused->getId(), $this->tabOrder);
        }

        // Find next valid component in tab order
        for ($i = 1; $i <= count($this->tabOrder); $i++) {
            $nextIndex = ($currentIndex + $i) % count($this->tabOrder);
            $componentId = $this->tabOrder[$nextIndex];
            
            if (isset($selectables[$componentId]) && $selectables[$componentId]->isSelectable()) {
                $this->setFocus($selectables[$componentId]);
                return true;
            }
        }

        return false;
    }

    private function focusPreviousInTabOrder(): bool
    {
        $selectables = $this->getSelectableComponentsById();
        $currentIndex = 0;

        if ($this->focused && $this->focused instanceof Component) {
            $found = array_search($this->focused->getId(), $this->tabOrder);
            if ($found !== false) {
                $currentIndex = $found;
            }
        }

        // Find previous valid component in tab order
        for ($i = 1; $i <= count($this->tabOrder); $i++) {
            $prevIndex = ($currentIndex - $i + count($this->tabOrder)) % count($this->tabOrder);
            $componentId = $this->tabOrder[$prevIndex];
            
            if (isset($selectables[$componentId]) && $selectables[$componentId]->isSelectable()) {
                $this->setFocus($selectables[$componentId]);
                return true;
            }
        }

        return false;
    }

    private function getSelectableComponentsById(): array
    {
        $components = [];
        
        foreach ($this->getSelectableComponents() as $component) {
            if ($component instanceof Component) {
                $components[$component->getId()] = $component;
            }
        }
        
        return $components;
    }
}