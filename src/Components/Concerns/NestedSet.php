<?php

namespace Crumbls\Tui\Components\Concerns;

use Crumbls\Tui\Components\Contracts\Component;
use Illuminate\Support\Collection;

trait NestedSet
{
	// =================== HIERARCHY MANAGEMENT ===================
    protected int $lft = 1;
    protected int $rgt = 2;
    protected int $depth = 0;
    protected ?Component $parent = null;
    protected array $scopes = [];
    protected static array $nodes = [];
    protected static int $nextPosition = 1;

    // =================== LARAVEL NESTEDSET INSPIRED API ===================

    /**
     * Get all ancestors of this node
     */
    public function ancestors(): Collection
    {
        return collect(static::$nodes)
            ->filter(fn($node) => $node->lft < $this->lft && $node->rgt > $this->rgt)
            ->sortBy('lft')
            ->values();
    }

    /**
     * Get all descendants of this node
     */
    public function descendants(): Collection
    {
        return collect(static::$nodes)
            ->filter(fn($node) => $node->lft > $this->lft && $node->rgt < $this->rgt)
            ->sortBy('lft')
            ->values();
    }

    /**
     * Get immediate children
     */
    public function children(): Collection
    {
        // Return children in insertion order (not sorted by lft)
        return collect(static::$nodes)
            ->filter(fn($node) => $node->parent === $this)
            ->values();
    }

    /**
     * Get siblings (same parent, excluding self)
     */
    public function siblings(): Collection
    {
        if (!$this->parent) {
            return collect();
        }

        return $this->parent->children()->reject(fn($node) => $node === $this);
    }

    /**
     * Check if this node is a descendant of another
     */
    public function isDescendantOf(Component $other): bool
    {
        return $this->lft > $other->lft && $this->rgt < $other->rgt;
    }

    /**
     * Check if this node is an ancestor of another
     */
    public function isAncestorOf(Component $other): bool
    {
        return $this->lft < $other->lft && $this->rgt > $other->rgt;
    }

    /**
     * Check if this is a root node
     */
    public function isRoot(): bool
    {
        return $this->parent === null;
    }

    /**
     * Check if this is a leaf node
     */
    public function isLeaf(): bool
    {
        return $this->rgt - $this->lft === 1;
    }

    /**
     * Get the root node
     */
    public function getRoot(): Component
    {
        return $this->ancestors()->first() ?? $this;
    }

    /**
     * Get component depth in tree (root = 0)
     */
    public function getDepth(): int
    {
        $this->ensureTreeBuilt();
        return $this->depth;
    }

    // =================== ADDITIONAL TRAVERSAL METHODS ===================

    /**
     * Get next sibling node
     */
    public function getNextSibling(): ?Component
    {
        if (!$this->parent) {
            return null;
        }

        $siblings = $this->parent->children()->values();
        $currentIndex = $siblings->search(fn($node) => $node === $this);
        
        if ($currentIndex !== false && $currentIndex < $siblings->count() - 1) {
            return $siblings[$currentIndex + 1];
        }
        
        return null;
    }

    /**
     * Get previous sibling node
     */
    public function getPrevSibling(): ?Component
    {
        if (!$this->parent) {
            return null;
        }

        $siblings = $this->parent->children()->values();
        $currentIndex = $siblings->search(fn($node) => $node === $this);
        
        if ($currentIndex !== false && $currentIndex > 0) {
            return $siblings[$currentIndex - 1];
        }
        
        return null;
    }

    /**
     * Get all next siblings
     */
    public function getNextSiblings(): Collection
    {
        if (!$this->parent) {
            return collect();
        }

        $siblings = $this->parent->children()->values();
        $currentIndex = $siblings->search(fn($node) => $node === $this);
        
        if ($currentIndex !== false) {
            return $siblings->slice($currentIndex + 1);
        }
        
        return collect();
    }

	public function getParent() : ?Component {
		return isset($this->parent) ? $this->parent : null;
	}

    /**
     * Get all previous siblings
     */
    public function getPrevSiblings(): Collection
    {
        if (!$this->parent) {
            return collect();
        }

        $siblings = $this->parent->children()->values();
        $currentIndex = $siblings->search(fn($node) => $node === $this);
        
        if ($currentIndex !== false) {
            return $siblings->slice(0, $currentIndex);
        }
        
        return collect();
    }

    /**
     * Check if this node is a child of another
     */
    public function isChildOf(Component $other): bool
    {
        return $this->parent === $other;
    }

    /**
     * Check if this node is a sibling of another
     */
    public function isSiblingOf(Component $other): bool
    {
        return $this->parent !== null && $this->parent === $other->parent && $this !== $other;
    }

    /**
     * Get first child
     */
    public function getFirstChild(): ?Component
    {
        return $this->children()->first();
    }

    /**
     * Get last child
     */
    public function getLastChild(): ?Component
    {
        return $this->children()->last();
    }

    /**
     * Check if node has children
     */
    public function hasChildren(): bool
    {
        return $this->children()->isNotEmpty();
    }

    /**
     * Count immediate children
     */
    public function countChildren(): int
    {
        return $this->children()->count();
    }

    /**
     * Count all descendants
     */
    public function countDescendants(): int
    {
        return $this->descendants()->count();
    }

    /**
     * Get node level (alias for getDepth)
     */
    public function getLevel(): int
    {
        return $this->depth;
    }

    /**
     * Get node height (maximum depth of descendants + 1)
     */
    public function getHeight(): int
    {
        if ($this->isLeaf()) {
            return 1;
        }

        $maxDescendantDepth = $this->descendants()->max(fn($node) => $node->getDepth());
        return $maxDescendantDepth - $this->depth + 1;
    }

    /**
     * Get subtree (node and all descendants)
     */
    public function getSubtree(): Collection
    {
        return collect([$this])->merge($this->descendants());
    }

    /**
     * Get path from root to this node
     */
    public function getPath(): Collection
    {
        return $this->ancestors()->push($this);
    }

    // =================== TREE BUILDING ===================

    /**
     * Append child to this node.
     * Note: Does NOT update lft/rgt. Call rebuildTree() when you need canonical values.
     */
    public function appendChild(Component $child): self
    {
        $child->parent = $this;
        $this->recalculateSubtreeDepth($child, $this->depth + 1);

        // Ensure parent is in the node list
        if (!in_array($this, static::$nodes, true)) {
            static::$nodes[] = $this;
        }
        // Ensure child is in the node list
        if (!in_array($child, static::$nodes, true)) {
            static::$nodes[] = $child;
        }
        return $this;
    }

    /**
     * Prepend child to this node.
     * Note: Does NOT update lft/rgt. Call rebuildTree() when you need canonical values.
     */
    public function prependChild(Component $child): self
    {
        $child->parent = $this;
        $this->recalculateSubtreeDepth($child, $this->depth + 1);
        // Ensure parent is in the node list
        if (!in_array($this, static::$nodes, true)) {
            static::$nodes[] = $this;
        }
        // Ensure child is in the node list (unshift for prepend)
        if (!in_array($child, static::$nodes, true)) {
            array_unshift(static::$nodes, $child);
        }
        return $this;
    }

    /**
     * Make this node a child of another.
     * Note: Does NOT update lft/rgt. Call rebuildTree() when you need canonical values.
     */
    public function makeChildOf(Component $parent): self
    {
        $this->parent = $parent;
        $this->recalculateSubtreeDepth($this, $parent->depth + 1);
        if (!in_array($parent, static::$nodes, true)) {
            static::$nodes[] = $parent;
        }
        if (!in_array($this, static::$nodes, true)) {
            static::$nodes[] = $this;
        }
        return $this;
    }

    /**
     * Make this node the root.
     * Note: Does NOT update lft/rgt. Call rebuildTree() when you need canonical values.
     */
    public function makeRoot(): self
    {
        $this->parent = null;
        $this->recalculateSubtreeDepth($this, 0);
        if (!in_array($this, static::$nodes, true)) {
            static::$nodes[] = $this;
        }
        return $this;
    }

    // =================== MOVEMENT OPERATIONS ===================

    /**
     * Move node to the left of another node (same level)
     */
    public function moveToLeftOf(Component $node): self
    {
        if ($node->parent === null) {
            // Moving to left of root - become root
            $this->makeRoot();
        } else {
            // Make this node a child of the target's parent
            $this->parent = $node->parent;
            $this->recalculateSubtreeDepth($this, $node->depth);
        }
        
        // Remove from current position in nodes array
        static::$nodes = array_values(array_filter(static::$nodes, fn($n) => $n !== $this));
        
        // Find target position and insert before it
        $targetIndex = array_search($node, static::$nodes, true);
        if ($targetIndex !== false) {
            array_splice(static::$nodes, $targetIndex, 0, [$this]);
        } else {
            static::$nodes[] = $this;
        }
        
        return $this;
    }

    /**
     * Move node to the right of another node (same level)
     */
    public function moveToRightOf(Component $node): self
    {
        if ($node->parent === null) {
            // Moving to right of root - become root
            $this->makeRoot();
        } else {
            // Make this node a child of the target's parent
            $this->parent = $node->parent;
            $this->recalculateSubtreeDepth($this, $node->depth);
        }
        
        // Remove from current position in nodes array
        static::$nodes = array_values(array_filter(static::$nodes, fn($n) => $n !== $this));
        
        // Find target position and insert after it
        $targetIndex = array_search($node, static::$nodes, true);
        if ($targetIndex !== false) {
            array_splice(static::$nodes, $targetIndex + 1, 0, [$this]);
        } else {
            static::$nodes[] = $this;
        }
        
        return $this;
    }

    /**
     * Insert node before another node (same level)
     */
    public function insertBeforeNode(Component $node): self
    {
        return $this->moveToLeftOf($node);
    }

    /**
     * Insert node after another node (same level)
     */
    public function insertAfterNode(Component $node): self
    {
        return $this->moveToRightOf($node);
    }

    /**
     * Append this node to another node (as child)
     */
    public function appendToNode(Component $node): self
    {
        return $this->makeChildOf($node);
    }

    /**
     * Prepend this node to another node (as first child)
     */
    public function prependToNode(Component $node): self
    {
        $this->parent = $node;
        $this->recalculateSubtreeDepth($this, $node->depth + 1);
        
        // Remove from current position
        static::$nodes = array_values(array_filter(static::$nodes, fn($n) => $n !== $this));
        
        // Add to beginning of nodes array
        array_unshift(static::$nodes, $this);
        
        if (!in_array($node, static::$nodes, true)) {
            static::$nodes[] = $node;
        }
        
        return $this;
    }

    /**
     * Move node up among siblings
     */
    public function moveUp(): self
    {
        if (!$this->parent) {
            return $this;
        }
        
        $allChildren = $this->parent->children()->values();
        $currentIndex = $allChildren->search(fn($node) => $node === $this);
        
        if ($currentIndex !== false && $currentIndex > 0) {
            $previousSibling = $allChildren->get($currentIndex - 1);
            $this->moveToLeftOf($previousSibling);
        }
        
        return $this;
    }

    /**
     * Move node down among siblings
     */
    public function moveDown(): self
    {
        if (!$this->parent) {
            return $this;
        }
        
        $allChildren = $this->parent->children()->values();
        $currentIndex = $allChildren->search(fn($node) => $node === $this);
        
        if ($currentIndex !== false && $currentIndex < $allChildren->count() - 1) {
            $nextSibling = $allChildren->get($currentIndex + 1);
            $this->moveToRightOf($nextSibling);
        }
        
        return $this;
    }

    // =================== INTERNAL TREE MANAGEMENT ===================

    /**
     * Recalculate depth for a node and all its descendants
     */
    protected function recalculateSubtreeDepth(Component $node, int $newDepth): void
    {
        $node->depth = $newDepth;
        
        foreach ($node->children() as $child) {
            $this->recalculateSubtreeDepth($child, $newDepth + 1);
        }
    }

    /**
     * Rebuild the entire tree structure (recalculate lft/rgt values)
     * Note: Must be called manually after making changes to the tree structure.
     */
    public static function rebuildTree(): void
    {
        static::$nextPosition = 1;
        $roots = array_filter(static::$nodes, fn($n) => $n->parent === null);
        foreach ($roots as $root) {
            static::rebuildNode($root);
        }
    }

    /**
     * Recursively rebuild a node and its children
     */
    protected static function rebuildNode(Component $node): void
    {
		if ($node->parent) {
			$node->depth = $node->parent->depth + 1;
		} else {
			$node->depth = 0;
		}
        $node->lft = static::$nextPosition++;

//		dd($node->children()->map(function($c) { return $c->getLft(); }));
		foreach ($node->children() as $child) {
            static::rebuildNode($child);
        }
        $node->rgt = static::$nextPosition++;
    }

    // =================== TREE VALIDATION AND MAINTENANCE ===================

    /**
     * Validate the entire tree structure
     */
    public static function validateTree(): bool
    {
        $errors = static::getTreeErrors();
        return empty($errors);
    }

    /**
     * Get all tree structure errors
     */
    public static function getTreeErrors(): array
    {
        $errors = [];
        $nodes = static::$nodes;

        // Check for orphaned nodes (nodes with parent not in tree)
        foreach ($nodes as $node) {
            if ($node->parent && !in_array($node->parent, $nodes, true)) {
                $errors[] = "Orphaned node found: {$node->getId()} has parent not in tree";
            }
        }

        // Check for circular references
        foreach ($nodes as $node) {
            if (static::hasCircularReference($node)) {
                $errors[] = "Circular reference detected for node: {$node->getId()}";
            }
        }

        // Check lft/rgt boundaries after rebuild
        static::rebuildTree();
        foreach ($nodes as $node) {
            if ($node->lft >= $node->rgt) {
                $errors[] = "Invalid boundaries for node {$node->getId()}: lft({$node->lft}) >= rgt({$node->rgt})";
            }
        }

        return $errors;
    }

    /**
     * Check if a node has circular references in its ancestry
     */
    protected static function hasCircularReference(Component $node): bool
    {
        $visited = [];
        $current = $node;

        while ($current !== null) {
            if (in_array($current, $visited, true)) {
                return true;
            }
            $visited[] = $current;
            $current = $current->parent;
        }

        return false;
    }

    /**
     * Fix tree structure issues
     */
    public static function fixTree(): array
    {
        $fixed = [];
        
        // Remove orphaned nodes
        $orphans = [];
        foreach (static::$nodes as $key => $node) {
            if ($node->parent && !in_array($node->parent, static::$nodes, true)) {
                $node->parent = null;
                $node->depth = 0;
                $orphans[] = $node->getId();
                $fixed[] = "Fixed orphaned node: {$node->getId()}";
            }
        }

        // Break circular references
        foreach (static::$nodes as $node) {
            if (static::hasCircularReference($node)) {
                $node->parent = null;
                $node->depth = 0;
                $fixed[] = "Broke circular reference for node: {$node->getId()}";
            }
        }

        // Rebuild tree to fix boundaries and depths
        static::rebuildTree();
        static::recalculateDepths();
        
        if (!empty($fixed)) {
            $fixed[] = "Rebuilt tree structure";
        }

        return $fixed;
    }

    /**
     * Recalculate depths for all nodes
     */
    protected static function recalculateDepths(): void
    {
        $roots = array_filter(static::$nodes, fn($n) => $n->parent === null);
        
        foreach ($roots as $root) {
            static::recalculateNodeDepth($root, 0);
        }
    }

    /**
     * Recursively recalculate depth for a node and its children
     */
    protected static function recalculateNodeDepth(Component $node, int $depth): void
    {
        $node->depth = $depth;
        
        foreach ($node->children() as $child) {
            static::recalculateNodeDepth($child, $depth + 1);
        }
    }

    /**
     * Remove node and all its descendants from tree
     */
    public function deleteSubtree(): self
    {
        $subtree = $this->getSubtree();
        
        // Remove all nodes in subtree from the global nodes array
        static::$nodes = array_values(array_filter(static::$nodes, function($node) use ($subtree) {
            return !$subtree->contains($node);
        }));
        
        // Clear parent reference
        $this->parent = null;
        
        return $this;
    }

    /**
     * Remove all children from this node (but keep this node)
     */
    public function clearChildren(): self
    {
        $children = $this->children();
        
        foreach ($children as $child) {
            $child->deleteSubtree();
        }
        
        return $this;
    }

    /**
     * Check if tree structure is consistent
     */
    public function isConsistent(): bool
    {
        // Check if this node's children all point to this as parent
        foreach ($this->children() as $child) {
            if ($child->parent !== $this) {
                return false;
            }
        }

        // Check if parent relationship is mutual
        if ($this->parent && !$this->parent->children()->contains($this)) {
            return false;
        }

        // Check depth consistency
        $expectedDepth = $this->parent ? $this->parent->depth + 1 : 0;
        if ($this->depth !== $expectedDepth) {
            return false;
        }

        return true;
    }

    /**
     * Get tree statistics
     */
    public static function getTreeStats(): array
    {
        $stats = [
            'total_nodes' => count(static::$nodes),
            'root_nodes' => 0,
            'leaf_nodes' => 0,
            'max_depth' => 0,
            'total_edges' => 0,
        ];

        foreach (static::$nodes as $node) {
            if ($node->isRoot()) {
                $stats['root_nodes']++;
            }
            
            if ($node->isLeaf()) {
                $stats['leaf_nodes']++;
            }
            
            if ($node->depth > $stats['max_depth']) {
                $stats['max_depth'] = $node->depth;
            }
            
            $stats['total_edges'] += $node->countChildren();
        }

        return $stats;
    }

    // =================== SCOPING FUNCTIONALITY ===================

    /**
     * Set scope attributes for this node
     */
    public function setScopes(array $scopes): self
    {
        $this->scopes = $scopes;
        return $this;
    }

    /**
     * Get scope attributes
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * Check if node has specific scope value
     */
    public function hasScope(string $key, $value = null): bool
    {
        if ($value === null) {
            return array_key_exists($key, $this->scopes);
        }
        
        return isset($this->scopes[$key]) && $this->scopes[$key] === $value;
    }

    /**
     * Get nodes within the same scope
     */
    public function getScopedNodes(): Collection
    {
        if (empty($this->scopes)) {
            return collect(static::$nodes);
        }

        return collect(static::$nodes)->filter(function($node) {
            foreach ($this->scopes as $key => $value) {
                if (!$node->hasScope($key, $value)) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Get scoped ancestors (ancestors within same scope)
     */
    public function getScopedAncestors(): Collection
    {
        return $this->ancestors()->filter(function($ancestor) {
            foreach ($this->scopes as $key => $value) {
                if (!$ancestor->hasScope($key, $value)) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Get scoped descendants (descendants within same scope)
     */
    public function getScopedDescendants(): Collection
    {
        return $this->descendants()->filter(function($descendant) {
            foreach ($this->scopes as $key => $value) {
                if (!$descendant->hasScope($key, $value)) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Get scoped siblings (siblings within same scope)
     */
    public function getScopedSiblings(): Collection
    {
        return $this->siblings()->filter(function($sibling) {
            foreach ($this->scopes as $key => $value) {
                if (!$sibling->hasScope($key, $value)) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Get scoped root node
     */
    public function getScopedRoot(): ?Component
    {
        $ancestors = $this->getScopedAncestors();
        
        if ($ancestors->isEmpty()) {
            return $this->isRoot() ? $this : null;
        }
        
        return $ancestors->first();
    }

    /**
     * Rebuild tree within specific scope
     */
    public static function rebuildScopedTree(array $scopes): void
    {
        static::$nextPosition = 1;
        
        $scopedNodes = collect(static::$nodes)->filter(function($node) use ($scopes) {
            foreach ($scopes as $key => $value) {
                if (!$node->hasScope($key, $value)) {
                    return false;
                }
            }
            return true;
        });
        
        $roots = $scopedNodes->filter(fn($n) => $n->parent === null || !$scopedNodes->contains($n->parent));
        
        foreach ($roots as $root) {
            static::rebuildScopedNode($root, $scopedNodes);
        }
    }

    /**
     * Recursively rebuild a scoped node and its children
     */
    protected static function rebuildScopedNode(Component $node, Collection $scopedNodes): void
    {
        $node->lft = static::$nextPosition++;
        
        $scopedChildren = $node->children()->filter(fn($child) => $scopedNodes->contains($child));
        
        foreach ($scopedChildren as $child) {
            static::rebuildScopedNode($child, $scopedNodes);
        }
        
        $node->rgt = static::$nextPosition++;
    }

    /**
     * Clear all nodes (useful for testing or resetting)
     */
    public static function clearNodes(): void
    {
        static::$nodes = [];
        static::$nextPosition = 1;
    }

    /**
     * Get all nodes in the tree
     */
    public static function getAllNodes(): array
    {
        return static::$nodes;
    }

    /**
     * Ensure tree boundaries are calculated (call rebuildTree if needed)
     */
    public function ensureTreeBuilt(): self
    {
        // Check if any node has invalid lft/rgt values
        $needsRebuild = false;
        foreach (static::$nodes as $node) {
            if ($node->lft === 1 && $node->rgt === 2 && !$node->isRoot()) {
                $needsRebuild = true;
                break;
            }
        }
        
        if ($needsRebuild) {
            static::rebuildTree();
        }
        
        return $this;
    }

    // =================== FLUENT FACTORY METHODS ===================

    /**
     * Create a tree structure using closure
     */
    public static function tree(callable $callback): Component
    {
        $builder = new static();
        $builder->makeRoot();
        
        $callback($builder);
        
        return $builder;
    }

    /**
     * Fluent method for adding children
     */
    public function with(Component ...$children): self
    {
        foreach ($children as $child) {
            $this->appendChild($child);
        }
        
        return $this;
    }

    // =================== DEBUGGING ===================

    /**
     * Get tree structure as array for debugging
     */
    public function toTree(): array
    {
        $result = [
            'id' => $this->getId(),
            'lft' => $this->lft,
            'rgt' => $this->rgt,
            'depth' => $this->depth,
            'children' => []
        ];
        
        foreach ($this->children() as $child) {
            $result['children'][] = $child->toTree();
        }
        
        return $result;
    }

	public function getLft(): int
	{
		$this->ensureTreeBuilt();
		return $this->lft;
	}

	public function getRgt(): int {
		$this->ensureTreeBuilt();
		return $this->rgt;
	}

	public function getWidth() : int {
		return $this->rgt - $this->lft;
	}
}