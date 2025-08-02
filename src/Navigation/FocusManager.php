<?php

declare(strict_types=1);

namespace Crumbls\Tui\Navigation;

use Crumbls\Tui\Contracts\FocusableInterface;

class FocusManager
{
    protected array $focusStack = [];
    protected ?FocusableInterface $currentFocus = null;

    /**
     * Set the root focusable component.
     */
    public function setRootFocus(FocusableInterface $component): void
    {
        $this->focusStack = [$component];
        $this->currentFocus = $component;
        $component->setFocus(true);
    }

    /**
     * Handle a key event by routing it to the focused component.
     */
    public function handleKey(string $key): bool
    {
        // Handle global focus navigation
        if ($key === "\t") { // Tab key
            return $this->moveFocusForward();
        }
        
        if ($key === "\033") { // Escape key
            return $this->moveFocusUp();
        }

        // Route to current focused component
        if ($this->currentFocus && $this->currentFocus->handleKey($key)) {
            return true;
        }

        // If not consumed, try parent components up the stack
        for ($i = count($this->focusStack) - 2; $i >= 0; $i--) {
            if ($this->focusStack[$i]->handleKey($key)) {
                return true;
            }
        }

        return false; // Not consumed
    }

    /**
     * Move focus forward (Tab key behavior).
     */
    protected function moveFocusForward(): bool
    {
        if (!$this->currentFocus) {
            return false;
        }

        $children = $this->currentFocus->getFocusableChildren();
        
        if (!empty($children)) {
            // Move focus down to first child
            $firstChild = $children[0];
            $this->pushFocus($firstChild);
            return true;
        }

        // No children - try to move back up to parent
        if (count($this->focusStack) > 1) {
            $this->popFocus();
            return true;
        }

        return false; // Already at root with no children
    }

    /**
     * Move focus up one level (Escape key behavior).
     */
    protected function moveFocusUp(): bool
    {
        if (count($this->focusStack) <= 1) {
            return false; // Already at root
        }

        $this->popFocus();
        return true;
    }

    /**
     * Push focus down to a child component.
     */
    protected function pushFocus(FocusableInterface $component): void
    {
        if ($this->currentFocus) {
            $this->currentFocus->setFocus(false);
        }

        $this->focusStack[] = $component;
        $this->currentFocus = $component;
        $component->setFocus(true);
    }

    /**
     * Pop focus back to parent component.
     */
    protected function popFocus(): void
    {
        if (count($this->focusStack) <= 1) {
            return;
        }

        if ($this->currentFocus) {
            $this->currentFocus->setFocus(false);
        }

        array_pop($this->focusStack);
        $this->currentFocus = end($this->focusStack);
        
        if ($this->currentFocus) {
            $this->currentFocus->setFocus(true);
        }
    }

    /**
     * Try to move focus to the next sibling component.
     */
    protected function moveFocusToNextSibling(): bool
    {
        if (count($this->focusStack) < 2) {
            return false;
        }

        $parent = $this->focusStack[count($this->focusStack) - 2];
        $siblings = $parent->getFocusableChildren();
        
        $currentIndex = array_search($this->currentFocus, $siblings, true);
        if ($currentIndex === false) {
            return false;
        }

        $nextIndex = ($currentIndex + 1) % count($siblings);
        if ($nextIndex === $currentIndex) {
            return false; // Only one child
        }

        // Switch to next sibling
        if ($this->currentFocus) {
            $this->currentFocus->setFocus(false);
        }

        $this->focusStack[count($this->focusStack) - 1] = $siblings[$nextIndex];
        $this->currentFocus = $siblings[$nextIndex];
        $this->currentFocus->setFocus(true);

        return true;
    }

    /**
     * Get the currently focused component.
     */
    public function getCurrentFocus(): ?FocusableInterface
    {
        return $this->currentFocus;
    }

    /**
     * Get the focus hierarchy stack.
     */
    public function getFocusStack(): array
    {
        return $this->focusStack;
    }
}