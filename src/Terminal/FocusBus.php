<?php

declare(strict_types=1);

namespace Crumbls\Tui\Terminal;

use Crumbls\Tui\Contracts\FocusBusContract;
use Crumbls\Tui\Contracts\SelectableInterface;
use Crumbls\Tui\Components\Contracts\Component;
use Crumbls\Tui\Events\MouseEvent;
use Crumbls\Tui\Events\FocusEnterEvent;
use Crumbls\Tui\Events\FocusLeaveEvent;
use Crumbls\Tui\Events\ActivateEvent;

/**
 * Manages focus and selection across component trees.
 * Ensures events only bubble from the currently focused component up through its parent chain.
 */
class FocusBus implements FocusBusContract
{
    private ?SelectableInterface $focused = null;
    private array $roots = [];
    private array $tabOrder = [];
    private bool $enabled = true;

    /**
     * Set focus to a specific component
     */
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

    /**
     * Get the currently focused component
     */
    public function getFocused(): ?SelectableInterface
    {
        return $this->focused;
    }

    /**
     * Clear focus from all components
     */
    public function clearFocus(): void
    {
        if ($this->focused) {
            $this->focused->setSelected(false);
            $this->focused = null;
        }
    }

    /**
     * Focus the next selectable component
     */
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

    /**
     * Focus the previous selectable component
     */
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

    /**
     * Register a root component for focus management
     */
    public function registerRoot(Component $root): void
    {
        $this->roots[$root->getId()] = $root;
    }

    /**
     * Unregister a root component
     */
    public function unregisterRoot(Component $root): void
    {
        unset($this->roots[$root->getId()]);
        
        // Clear focus if the focused component was in this root
        if ($this->focused && $this->isComponentInTree($this->focused, $root)) {
            $this->clearFocus();
        }
    }

    /**
     * Get the selectable component at specific coordinates (for mouse clicks)
     */
    public function getComponentAt(int $x, int $y): ?SelectableInterface
    {
        $candidates = [];

        foreach ($this->roots as $root) {
            $component = $this->findComponentAtPosition($root, $x, $y);
            if ($component && $component instanceof SelectableInterface && $component->isSelectable()) {
                $candidates[] = $component;
            }
        }

        // Return the component with highest selection priority
        if (!empty($candidates)) {
            usort($candidates, fn($a, $b) => $b->getSelectionPriority() <=> $a->getSelectionPriority());
            return $candidates[0];
        }

        return null;
    }

    /**
     * Handle mouse click and set focus appropriately
     */
    public function handleMouseClick(MouseEvent $event): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $component = $this->getComponentAt($event->x, $event->y);
        if ($component && $component->canReceiveMouse()) {
            $this->setFocus($component);
            
            // Let the component handle the mouse event
            return $component->handleMouseInput($event);
        }

        return false;
    }

    /**
     * Handle key input - routes to focused component and bubbles up if not handled
     */
    public function handleKeyInput(string $key): bool
    {
        if (!$this->enabled || !$this->focused) {
            return false;
        }

        // Start with focused component and bubble up through parent chain
        $current = $this->focused;
        
        while ($current) {
            if ($current->canReceiveKeyboard() && $current->handleKeyInput($key)) {
                return true; // Event was handled
            }
            
            // Bubble up to parent if current component extends Component
            if ($current instanceof Component && $current->getParent()) {
                $parent = $current->getParent();
                if ($parent instanceof SelectableInterface) {
                    $current = $parent;
                } else {
                    break;
                }
            } else {
                break;
            }
        }

        return false; // Event not handled
    }

    /**
     * Get all selectable components from registered roots
     */
    public function getSelectableComponents(): array
    {
        $components = [];

        foreach ($this->roots as $root) {
            $this->collectSelectableComponents($root, $components);
        }

        // Sort by selection priority and position
        usort($components, function($a, $b) {
            $priorityDiff = $b->getSelectionPriority() <=> $a->getSelectionPriority();
            if ($priorityDiff !== 0) {
                return $priorityDiff;
            }
            
            // If same priority, maintain document order
            return 0;
        });

        return $components;
    }

    /**
     * Set explicit tab order for components
     */
    public function setTabOrder(array $componentIds): void
    {
        $this->tabOrder = $componentIds;
    }

    /**
     * Get current tab order
     */
    public function getTabOrder(): array
    {
        return $this->tabOrder;
    }

    /**
     * Enable or disable focus management
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
        if (!$enabled) {
            $this->clearFocus();
        }
    }

    /**
     * Check if focus management is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Recursively collect selectable components from a tree
     */
    private function collectSelectableComponents(Component $component, array &$components): void
    {
        if ($component instanceof SelectableInterface && $component->isSelectable()) {
            $components[] = $component;
        }

        foreach ($component->children() as $child) {
            $this->collectSelectableComponents($child, $components);
        }
    }

    /**
     * Find component at specific position (simplified for now)
     */
    private function findComponentAtPosition(Component $component, int $x, int $y): ?Component
    {
        // This would need to be implemented based on your layout system
        // For now, just return the component if it matches coordinates
        if ($component instanceof SelectableInterface && $component->isSelectable()) {
            return $component;
        }

        // Search children
        foreach ($component->children() as $child) {
            $found = $this->findComponentAtPosition($child, $x, $y);
            if ($found) {
                return $found;
            }
        }

        return null;
    }

    /**
     * Check if a component is within a tree
     */
    private function isComponentInTree(SelectableInterface $component, Component $root): bool
    {
        if ($component === $root) {
            return true;
        }

        foreach ($root->children() as $child) {
            if ($this->isComponentInTree($component, $child)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Focus next component in explicit tab order
     */
    private function focusNextInTabOrder(): bool
    {
        $selectables = $this->getSelectableComponentsById();
        $currentIndex = -1;

        if ($this->focused) {
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

    /**
     * Focus previous component in explicit tab order
     */
    private function focusPreviousInTabOrder(): bool
    {
        $selectables = $this->getSelectableComponentsById();
        $currentIndex = 0;

        if ($this->focused) {
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

    /**
     * Get selectable components indexed by ID
     */
    private function getSelectableComponentsById(): array
    {
        $components = [];
        
        foreach ($this->getSelectableComponents() as $component) {
            $components[$component->getId()] = $component;
        }
        
        return $components;
    }

    // =================== ENHANCED COMPONENT FOCUS SYSTEM ===================

    protected ?Component $focusedComponent = null;

    /**
     * Set focus on a Component (with new event system)
     */
    public function setComponentFocus(Component $component): void
    {
        if (!$this->enabled || !$component->canFocus()) {
            return;
        }

        $previousComponent = $this->focusedComponent;

        // Dispatch leave event to previous component
        if ($previousComponent && $previousComponent !== $component) {
            $leaveEvent = new FocusLeaveEvent($previousComponent, $component);
            $previousComponent->dispatchLeaveEvent($leaveEvent);
        }

        // Set new focus
        $this->focusedComponent = $component;

        // Dispatch enter event to new component
        $enterEvent = new FocusEnterEvent($component, $previousComponent);
        $component->dispatchEnterEvent($enterEvent);
    }

    /**
     * Get the currently focused Component
     */
    public function getFocusedComponent(): ?Component
    {
        return $this->focusedComponent;
    }

    /**
     * Clear component focus
     */
    public function clearComponentFocus(): void
    {
        if ($this->focusedComponent) {
            $leaveEvent = new FocusLeaveEvent($this->focusedComponent);
            $this->focusedComponent->dispatchLeaveEvent($leaveEvent);
            $this->focusedComponent = null;
        }
    }

    /**
     * Activate the currently focused component (Enter key pressed)
     */
    public function activateFocusedComponent(string $trigger = 'enter'): bool
    {
        if (!$this->focusedComponent) {
            return false;
        }

        $activateEvent = new ActivateEvent($this->focusedComponent, $trigger);
        return $this->focusedComponent->dispatchActivateEvent($activateEvent);
    }

    /**
     * Focus next focusable component
     */
    public function focusNextComponent(): bool
    {
        $focusableComponents = $this->getFocusableComponents();
        if (empty($focusableComponents)) {
            return false;
        }

        // Find current focus index
        $currentIndex = -1;
        if ($this->focusedComponent) {
            foreach ($focusableComponents as $index => $component) {
                if ($component->getId() === $this->focusedComponent->getId()) {
                    $currentIndex = $index;
                    break;
                }
            }
        }

        // Get next component (with wrap-around)
        $nextIndex = ($currentIndex + 1) % count($focusableComponents);
        $this->setComponentFocus($focusableComponents[$nextIndex]);

        return true;
    }

    /**
     * Focus previous focusable component
     */
    public function focusPreviousComponent(): bool
    {
        $focusableComponents = $this->getFocusableComponents();
        if (empty($focusableComponents)) {
            return false;
        }

        // Find current focus index
        $currentIndex = -1;
        if ($this->focusedComponent) {
            foreach ($focusableComponents as $index => $component) {
                if ($component->getId() === $this->focusedComponent->getId()) {
                    $currentIndex = $index;
                    break;
                }
            }
        }

        // Get previous component (with wrap-around)
        $prevIndex = ($currentIndex - 1 + count($focusableComponents)) % count($focusableComponents);
        $this->setComponentFocus($focusableComponents[$prevIndex]);

        return true;
    }

    /**
     * Get all focusable components from registered roots
     */
    public function getFocusableComponents(): array
    {
        $focusableComponents = [];
        
        foreach ($this->roots as $root) {
            $this->collectFocusableComponents($root, $focusableComponents);
        }
        
        return $focusableComponents;
    }

    /**
     * Recursively collect focusable components
     */
    private function collectFocusableComponents(Component $component, array &$components): void
    {
        if ($component->canFocus()) {
            $components[] = $component;
        }

        foreach ($component->children() as $child) {
            $this->collectFocusableComponents($child, $components);
        }
    }
}