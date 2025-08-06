<?php

namespace Crumbls\Tui\Components\Concerns;

use Crumbls\Tui\Components\Contracts\Component;

trait HasHierarchy
{
    protected ?Component $parent = null;
    protected array $children = [];
    protected string $id;

    // =================== HIERARCHY MANAGEMENT ===================

    public function getParent(): ?Component
    {
        return $this->parent;
    }

    public function setParent(?Component $parent): self
    {
        // Remove from old parent if exists
        if ($this->parent && $this->parent !== $parent) {
            $this->parent->removeChild($this);
        }

        $this->parent = $parent;

        // Add to new parent if exists
        if ($parent && !in_array($this, $parent->getChildren(), true)) {
            $parent->addChild($this);
        }

        return $this;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function addChild(Component $child): self
    {
        if (!in_array($child, $this->children, true)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(Component $child): self
    {
        $index = array_search($child, $this->children, true);
        if ($index !== false) {
            unset($this->children[$index]);
            $this->children = array_values($this->children); // Re-index
            
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function hasChildren(): bool
    {
        return !empty($this->children);
    }

    public function getDepth(): int
    {
        $depth = 0;
        $current = $this->parent;
        
        while ($current) {
            $depth++;
            $current = $current->getParent();
        }
        
        return $depth;
    }

    public function findById(string $id): ?Component
    {
        if ($this->getId() === $id) {
            return $this;
        }

        foreach ($this->children as $child) {
            $found = $child->findById($id);
            if ($found) {
                return $found;
            }
        }

        return null;
    }

    // =================== TRAVERSAL ===================

    public function getAncestors(): array
    {
        $ancestors = [];
        $current = $this->parent;
        
        while ($current) {
            $ancestors[] = $current;
            $current = $current->getParent();
        }
        
        return $ancestors;
    }

    public function getDescendants(): array
    {
        $descendants = [];
        
        foreach ($this->children as $child) {
            $descendants[] = $child;
            $descendants = array_merge($descendants, $child->getDescendants());
        }
        
        return $descendants;
    }

    public function getSiblings(): array
    {
        if (!$this->parent) {
            return [];
        }
        
        return array_filter(
            $this->parent->getChildren(),
            fn($child) => $child !== $this
        );
    }

    public function getRoot(): Component
    {
        $current = $this;
        
        while ($current->getParent()) {
            $current = $current->getParent();
        }
        
        return $current;
    }

    // =================== BASIC PROPERTIES ===================

    public function getId(): string
    {
        return $this->id ?? uniqid('component_');
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }
}