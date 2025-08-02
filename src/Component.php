<?php

declare(strict_types=1);

namespace Crumbls\Tui;

use Crumbls\Tui\Contracts\ComponentInterface;
use Crumbls\Tui\Contracts\InputEventInterface;
use Crumbls\Tui\Concerns\HasPosition;
use Crumbls\Tui\Concerns\IsSelectable;

/**
 * Base component class with position and selection capabilities.
 */
abstract class Component implements ComponentInterface
{
    use HasPosition;
    use IsSelectable;

    protected string $id;
    protected array $children = [];
    protected array $attributes = [];
    protected bool $visible = true;

    public function __construct()
    {
        $this->id = uniqid('component_', true);
    }

    public static function make(): static
    {
        return new static();
    }

    /**
     * Render the component content.
     */
    abstract public function render(): string;

    /**
     * Get the unique component ID.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set the component ID.
     */
    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Check if the component is visible.
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * Set the visibility of the component.
     */
    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * Add a child component.
     */
    public function addChild(ComponentInterface $child): static
    {
        $child->setParent($this);
        $this->children[$child->getId()] = $child;
        return $this;
    }

    /**
     * Remove a child component.
     */
    public function removeChild(string $childId): static
    {
        if (isset($this->children[$childId])) {
            $this->children[$childId]->setParent(null);
            unset($this->children[$childId]);
        }
        return $this;
    }

    /**
     * Get all child components.
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Get a child component by ID.
     */
    public function getChild(string $childId): ?ComponentInterface
    {
        return $this->children[$childId] ?? null;
    }

    /**
     * Find the topmost component at a given point (accounting for z-index/priority).
     */
    public function getComponentAt(int $x, int $y): ?ComponentInterface
    {
        // Check children first (they render on top)
        $candidates = [];
        
        foreach ($this->children as $child) {
            if ($child->isVisible() && $child->containsPoint($x, $y)) {
                $childResult = $child->getComponentAt($x, $y);
                if ($childResult) {
                    $candidates[] = $childResult;
                } else {
                    $candidates[] = $child;
                }
            }
        }

        // If we have candidates, return the one with highest selection priority
        if (!empty($candidates)) {
            usort($candidates, fn($a, $b) => $b->getSelectionPriority() <=> $a->getSelectionPriority());
            return $candidates[0];
        }

        // Check if this component itself contains the point
        if ($this->isVisible() && $this->containsPoint($x, $y)) {
            return $this;
        }

        return null;
    }

    /**
     * Get all selectable components within this component tree.
     */
    public function getSelectableComponents(): array
    {
        $components = [];

        if ($this->isSelectable() && $this->isVisible()) {
            $components[] = $this;
        }

        foreach ($this->children as $child) {
            $components = array_merge($components, $child->getSelectableComponents());
        }

        return $components;
    }

    /**
     * Set an attribute value.
     */
    public function setAttribute(string $key, mixed $value): static
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Get an attribute value.
     */
    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Check if an attribute exists.
     */
    public function hasAttribute(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Remove an attribute.
     */
    public function removeAttribute(string $key): static
    {
        unset($this->attributes[$key]);
        return $this;
    }

    /**
     * Get all attributes.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Handle mouse input - override in specific components.
     */
    public function handleMouseInput(InputEventInterface $event): bool
    {
        // Default behavior: if this component contains the mouse position,
        // select it (if selectable)
        if ($event->getInputType() === 'mouse' && 
            $this->canReceiveMouse() && 
            $this->containsPoint($event->getX(), $event->getY())) {
            
            if ($this->isSelectable()) {
                $this->setSelected(true);
                return true;
            }
        }
        
        return false;
    }

    /**
     * Handle key input - override in specific components.
     */
    public function handleKeyInput(InputEventInterface $event): bool
    {
        // Default behavior: do nothing
        return false;
    }

    /**
     * Get debug information about this component.
     */
    public function getDebugInfo(): array
    {
        $bounds = $this->getAbsoluteBounds();
        
        return [
            'id' => $this->id,
            'class' => static::class,
            'bounds' => $bounds,
            'visible' => $this->visible,
            'selectable' => $this->selectable,
            'selected' => $this->selected,
            'priority' => $this->selectionPriority,
            'children_count' => count($this->children),
            'attributes' => $this->attributes,
        ];
    }
}