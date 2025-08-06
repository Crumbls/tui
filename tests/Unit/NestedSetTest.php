<?php

declare(strict_types=1);

use Crumbls\Tui\Components\Contracts\Component;
use Crumbls\Tui\Components\Concerns\NestedSet;

require_once(__DIR__.'/../Support/ComponentStub.php');

beforeEach(function () {
	ComponentStub::clearNodes();
});

it('builds a nested tree with correct positions and relationships', function () {
	$root = new ComponentStub('root');
	$root->makeRoot();

	$child1 = new ComponentStub('child1');
	$child2 = new ComponentStub('child2');
	$child3 = new ComponentStub('child3');

	$root->appendChild($child1);
	$root->appendChild($child2);
	$child1->appendChild($child3);

	$root::rebuildTree();

	// Check positions
	expect($root->getLft())->toBe(1)
		->and($root->getRgt())->toBe(8)
		->and($child1->getLft())->toBe(2)
		->and($child1->getRgt())->toBe(5)
		->and($child2->getLft())->toBe(6)
		->and($child2->getRgt())->toBe(7)
		->and($child3->getLft())->toBe(3)
		->and($child3->getRgt())->toBe(4);

	// Check depths
	expect($root->getDepth())->toBe(0)
		->and($child1->getDepth())->toBe(1)
		->and($child3->getDepth())->toBe(2);

	// Check relationships
	expect($child1->ancestors())->toHaveCount(1)
		->and($child1->ancestors()->first())->toBe($root)
		->and($child3->ancestors()->map(fn($n) => $n->getId()))->toContain('child1', 'root');

	expect($root->descendants())->toHaveCount(3)
		->and($root->children())->toHaveCount(2)
		->and($child1->children()->first())->toBe($child3);

	expect($child2->siblings())->toHaveCount(1)
		->and($child2->siblings()->first())->toBe($child1);

	// Check root lookup
	expect($child3->getRoot())->toBe($root);
});

it('handles movement operations correctly', function () {
	$root = new ComponentStub('root');
	$root->makeRoot();

	$child1 = new ComponentStub('child1');
	$child2 = new ComponentStub('child2');
	$child3 = new ComponentStub('child3');

	$root->appendChild($child1);
	$root->appendChild($child2);
	$root->appendChild($child3);

	$root::rebuildTree();

	// Test moveToLeftOf
	$child3->moveToLeftOf($child1);
	$root::rebuildTree();

	$children = $root->children()->values()->all();
	expect($children[0])->toBe($child3)
		->and($children[1])->toBe($child1)
		->and($children[2])->toBe($child2);

	// Test moveToRightOf
	$child1->moveToRightOf($child2);
	$root::rebuildTree();

	$children = $root->children()->values()->all();
	expect($children[0])->toBe($child3)
		->and($children[1])->toBe($child2)
		->and($children[2])->toBe($child1);
});

it('supports makeChildOf operation', function () {
	$root = new ComponentStub('root');
	$root->makeRoot();

	$child1 = new ComponentStub('child1');
	$child2 = new ComponentStub('child2');

	$root->appendChild($child1);
	$root->appendChild($child2);

	// Move child2 to be child of child1
	$child2->makeChildOf($child1);
	$root::rebuildTree();

	expect($root->countChildren())->toBe(1)
		->and($child1->countChildren())->toBe(1)
		->and($child2->isChildOf($child1))->toBeTrue()
		->and($child2->getDepth())->toBe(2);
});

it('provides comprehensive traversal methods', function () {
	$root = new ComponentStub('root');
	$root->makeRoot();

	$child1 = new ComponentStub('child1');
	$child2 = new ComponentStub('child2');
	$grandchild = new ComponentStub('grandchild');

	$root->appendChild($child1);
	$root->appendChild($child2);
	$child1->appendChild($grandchild);

	$root::rebuildTree();

	// Test next/prev siblings
	expect($child1->getNextSibling())->toBe($child2)
		->and($child2->getPrevSibling())->toBe($child1)
		->and($child1->getPrevSibling())->toBeNull()
		->and($child2->getNextSibling())->toBeNull();

	// Test sibling collections
	expect($child1->getNextSiblings())->toHaveCount(1)
		->and($child1->getNextSiblings()->first())->toBe($child2)
		->and($child2->getPrevSiblings())->toHaveCount(1)
		->and($child2->getPrevSiblings()->first())->toBe($child1);

	// Test path
	$path = $grandchild->getPath();
	expect($path)->toHaveCount(3)
		->and($path[0])->toBe($root)
		->and($path[1])->toBe($child1)
		->and($path[2])->toBe($grandchild);
});

it('detects node types correctly', function () {
	$root = new ComponentStub('root');
	$root->makeRoot();

	$child = new ComponentStub('child');
	$grandchild = new ComponentStub('grandchild');

	$root->appendChild($child);
	$child->appendChild($grandchild);

	$root::rebuildTree();

	// Test root detection
	expect($root->isRoot())->toBeTrue()
		->and($child->isRoot())->toBeFalse();

	// Test leaf detection
	expect($root->isLeaf())->toBeFalse()
		->and($child->isLeaf())->toBeFalse()
		->and($grandchild->isLeaf())->toBeTrue();

	// Test has children
	expect($root->hasChildren())->toBeTrue()
		->and($child->hasChildren())->toBeTrue()
		->and($grandchild->hasChildren())->toBeFalse();

	// Test first/last child
	expect($root->getFirstChild())->toBe($child)
		->and($root->getLastChild())->toBe($child)
		->and($child->getFirstChild())->toBe($grandchild);
});

it('calculates height correctly', function () {
	$root = new ComponentStub('root');
	$root->makeRoot();

	$child = new ComponentStub('child');
	$grandchild = new ComponentStub('grandchild');
	$greatGrandchild = new ComponentStub('greatGrandchild');

	$root->appendChild($child);
	$child->appendChild($grandchild);
	$grandchild->appendChild($greatGrandchild);

	$root::rebuildTree();

	expect($root->getTreeHeight())->toBe(4)
		->and($child->getTreeHeight())->toBe(3)
		->and($grandchild->getTreeHeight())->toBe(2)
		->and($greatGrandchild->getTreeHeight())->toBe(1);
});

it('manages subtrees correctly', function () {
	$root = new ComponentStub('root');
	$root->makeRoot();

	$child1 = new ComponentStub('child1');
	$child2 = new ComponentStub('child2');
	$grandchild = new ComponentStub('grandchild');

	$root->appendChild($child1);
	$root->appendChild($child2);
	$child1->appendChild($grandchild);

	$root::rebuildTree();

	// Test subtree
	$subtree = $child1->getSubtree();
	expect($subtree)->toHaveCount(2)
		->and($subtree->contains($child1))->toBeTrue()
		->and($subtree->contains($grandchild))->toBeTrue()
		->and($subtree->contains($child2))->toBeFalse();

	// Test delete subtree
	$initialStats = $root::getTreeStats();
	$child1->deleteSubtree();
	$newStats = $root::getTreeStats();

	expect($newStats['total_nodes'])->toBe($initialStats['total_nodes'] - 2)
		->and($root->countChildren())->toBe(1);
});

it('supports scoping functionality', function () {
	// Create nodes in main scope
	$mainRoot = new ComponentStub('mainRoot');
	$mainRoot->setScopes(['category' => 'main'])->makeRoot();
	$mainChild = new ComponentStub('mainChild');
	$mainChild->setScopes(['category' => 'main']);
	$mainRoot->appendChild($mainChild);

	// Create nodes in sidebar scope
	$sidebarRoot = new ComponentStub('sidebarRoot');
	$sidebarRoot->setScopes(['category' => 'sidebar'])->makeRoot();
	$sidebarChild = new ComponentStub('sidebarChild');
	$sidebarChild->setScopes(['category' => 'sidebar']);
	$sidebarRoot->appendChild($sidebarChild);

	$mainRoot::rebuildTree();

	// Test scope filtering
	$mainNodes = $mainRoot->getScopedNodes();
	$sidebarNodes = $sidebarRoot->getScopedNodes();

	expect($mainNodes)->toHaveCount(2)
		->and($sidebarNodes)->toHaveCount(2);

	// Test scoped descendants
	$mainDescendants = $mainRoot->getScopedDescendants();
	expect($mainDescendants)->toHaveCount(1)
		->and($mainDescendants->contains($mainChild))->toBeTrue();

	// Test scope methods
	expect($mainRoot->hasScope('category'))->toBeTrue()
		->and($mainRoot->hasScope('category', 'main'))->toBeTrue()
		->and($mainRoot->hasScope('category', 'sidebar'))->toBeFalse();
});

it('validates tree structure', function () {
	$root = new ComponentStub('root');
	$root->makeRoot();

	$child = new ComponentStub('child');
	$root->appendChild($child);

	$root::rebuildTree();

	expect($root::validateTree())->toBeTrue()
		->and($root::getTreeErrors())->toBeEmpty()
		->and($root->isConsistent())->toBeTrue()
		->and($child->isConsistent())->toBeTrue();
});

it('moves nodes up and down among siblings', function () {
	$root = new ComponentStub('root');
	$root->makeRoot();

	$child1 = new ComponentStub('child1');
	$child2 = new ComponentStub('child2');
	$child3 = new ComponentStub('child3');

	$root->appendChild($child1);
	$root->appendChild($child2);
	$root->appendChild($child3);

	$root::rebuildTree();

	// Test move down
	$child1->moveDown();
	$root::rebuildTree();

	$children = $root->children()->values()->all();
	expect($children[0])->toBe($child2)
		->and($children[1])->toBe($child1);

	// Test move up
	$child3->moveUp();
	$root::rebuildTree();

	$children = $root->children()->values()->all();
	expect($children[0])->toBe($child2)
		->and($children[1])->toBe($child3)
		->and($children[2])->toBe($child1);
});

it('provides accurate tree statistics', function () {
	$root = new ComponentStub('root');
	$root->makeRoot();

	$child1 = new ComponentStub('child1');
	$child2 = new ComponentStub('child2');
	$grandchild = new ComponentStub('grandchild');

	$root->appendChild($child1);
	$root->appendChild($child2);
	$child1->appendChild($grandchild);

	$root::rebuildTree();

	$stats = $root::getTreeStats();

	expect($stats['total_nodes'])->toBe(4)
		->and($stats['root_nodes'])->toBe(1)
		->and($stats['leaf_nodes'])->toBe(2) // child2 and grandchild
		->and($stats['max_depth'])->toBe(2)
		->and($stats['total_edges'])->toBe(3); // root->child1, root->child2, child1->grandchild
});

it('supports fluent tree creation', function () {
	$tree = ComponentStub::tree(function($root) {
		$child1 = new ComponentStub('child1');
		$child2 = new ComponentStub('child2');

		$root->with($child1, $child2);

		$grandchild = new ComponentStub('grandchild');
		$child1->appendChild($grandchild);
	});

	$tree::rebuildTree();

	expect($tree->isRoot())->toBeTrue()
		->and($tree->countChildren())->toBe(2)
		->and($tree->children()->first()->countChildren())->toBe(1);
});

it('maintains correct lft/rgt boundaries', function () {
	$root = new ComponentStub('root');
	$root->makeRoot();

	$child1 = new ComponentStub('child1');
	$child2 = new ComponentStub('child2');
	$grandchild = new ComponentStub('grandchild');

	$root->appendChild($child1);
	$root->appendChild($child2);
	$child1->appendChild($grandchild);

	$root::rebuildTree();

	expect($root->getLft())->toBe(1)
		->and($root->getRgt())->toBe(8)
		->and($root->getNestedSetWidth())->toBe(7)
		->and($child1->getLft())->toBe(2)
		->and($child1->getRgt())->toBe(5)
		->and($child1->getNestedSetWidth())->toBe(3)
		->and($grandchild->getLft())->toBe(3)
		->and($grandchild->getRgt())->toBe(4)
		->and($grandchild->getNestedSetWidth())->toBe(1)
		->and($child2->getLft())->toBe(6)
		->and($child2->getRgt())->toBe(7)
		->and($child2->getNestedSetWidth())->toBe(1);
});

it('clears nodes correctly', function () {
	$root = new ComponentStub('root');
	$root->makeRoot();

	$child = new ComponentStub('child');
	$root->appendChild($child);

	expect($root::getTreeStats()['total_nodes'])->toBe(2);

	$root::clearNodes();

	expect($root::getTreeStats()['total_nodes'])->toBe(0);
});

it('handles ancestor descendant relationships correctly', function () {
	$root = new ComponentStub('root');
	$root->makeRoot();

	$child = new ComponentStub('child');
	$grandchild = new ComponentStub('grandchild');

	$root->appendChild($child);
	$child->appendChild($grandchild);

	$root::rebuildTree();

	expect($grandchild->isDescendantOf($root))->toBeTrue()
		->and($grandchild->isDescendantOf($child))->toBeTrue()
		->and($root->isAncestorOf($grandchild))->toBeTrue()
		->and($child->isAncestorOf($grandchild))->toBeTrue()
		->and($child->isDescendantOf($grandchild))->toBeFalse()
		->and($grandchild->isAncestorOf($child))->toBeFalse();
});

it('handles sibling relationships correctly', function () {
	$root = new ComponentStub('root');
	$root->makeRoot();

	$child1 = new ComponentStub('child1');
	$child2 = new ComponentStub('child2');
	$child3 = new ComponentStub('child3');

	$root->appendChild($child1);
	$root->appendChild($child2);
	$root->appendChild($child3);

	$root::rebuildTree();

	expect($child1->isSiblingOf($child2))->toBeTrue()
		->and($child2->isSiblingOf($child3))->toBeTrue()
		->and($child1->isSiblingOf($child3))->toBeTrue()
		->and($child1->isSiblingOf($root))->toBeFalse()
		->and($child1->siblings())->toHaveCount(2);
});

it('calculates depth immediately when adding nodes without rebuild', function () {
	$root = new ComponentStub('root');
	$root->makeRoot();

	// Verify root depth immediately
	expect($root->getDepth())->toBe(0);

	$child1 = new ComponentStub('child1');
	$root->appendChild($child1);

	// Verify child depth is set immediately
	expect($child1->getDepth())->toBe(1);

	$grandchild = new ComponentStub('grandchild');
	$child1->appendChild($grandchild);

	// Verify grandchild depth is set immediately
	expect($grandchild->getDepth())->toBe(2);

	// Add a great-grandchild to test deeper nesting
	$greatGrandchild = new ComponentStub('great-grandchild');
	$grandchild->appendChild($greatGrandchild);

	expect($greatGrandchild->getDepth())->toBe(3);

	// Test moving nodes and depth recalculation
	$child2 = new ComponentStub('child2');
	$root->appendChild($child2);

	// Move grandchild to be child of child2
	$grandchild->makeChildOf($child2);

	// Verify all depths are correct after move
	expect($grandchild->getDepth())->toBe(2)
		->and($greatGrandchild->getDepth())->toBe(3);
});
