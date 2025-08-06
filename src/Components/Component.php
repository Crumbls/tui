<?php

namespace Crumbls\Tui\Components;

use Crumbls\Tui\Components\Contracts\Component as ComponentContract;
use Crumbls\Tui\Components\Concerns\NestedSet;

abstract class Component implements ComponentContract
{
    use NestedSet;

    protected string $id;
	protected string $title = '';
	// Auto-sizing properties
	protected int $x = 0;
	protected int $y = 0;
	protected int $width = 0;
	protected int $height = 0;
	protected bool $autoSize = true;
	protected bool $explicitWidth = false;
	protected bool $explicitHeight = false;

    public function __construct(string $id = null)
    {
        $this->id = $id ?? $this->generateId();
        
        // Initialize nested set properties
        $this->parent = null;
        $this->depth = 0;
    }

	public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    protected function generateId(): string
    {
        return strtolower(class_basename(static::class)) . '_' . uniqid();
    }


	public function title(string $title): self
	{
		$this->title = $title;
		return $this;
	}

	public function getTitle(): string
	{
		return $this->title;
	}
	
	/**
	 * Set explicit size (disables auto-sizing for those dimensions)
	 */
	public function size(int $width, int $height): self
	{
		$this->width = $width;
		$this->height = $height;
		$this->explicitWidth = true;
		$this->explicitHeight = true;
		return $this;
	}
	
	/**
	 * Set explicit width only
	 */
	public function width(int $width): self
	{
		$this->width = $width;
		$this->explicitWidth = true;
		return $this;
	}
	
	/**
	 * Set explicit height only
	 */
	public function height(int $height): self
	{
		$this->height = $height;
		$this->explicitHeight = true;
		return $this;
	}
	
	/**
	 * Get calculated width (auto-size if not explicit)
	 */
	public function getWidth(): int
	{
		if (!$this->explicitWidth && $this->parent) {
			return $this->calculateAutoWidth();
		}
		return $this->width ?: 20; // Default fallback
	}
	
	/**
	 * Get calculated height (auto-size if not explicit)
	 */
	public function getHeight(): int
	{
		if (!$this->explicitHeight && $this->parent) {
			return $this->calculateAutoHeight();
		}
		return $this->height ?: 10; // Default fallback
	}
	
	/**
	 * Calculate auto width based on parent's available space
	 */
	protected function calculateAutoWidth(): int
	{
		if (!$this->parent) {
			return $this->width ?: 20;
		}
		
		// Use parent's available width for child components
		if (method_exists($this->parent, 'getAvailableWidth')) {
			return $this->parent->getAvailableWidth();
		}
		
		// Fallback to parent's full width
		if (method_exists($this->parent, 'getWidth')) {
			return $this->parent->getWidth();
		}
		
		return $this->width ?: 20;
	}
	
	/**
	 * Calculate auto height based on parent's available space
	 */
	protected function calculateAutoHeight(): int
	{
		if (!$this->parent || !method_exists($this->parent, 'getAvailableHeightPerChild')) {
			return $this->height ?: 10;
		}
		
		return $this->parent->getAvailableHeightPerChild();
	}
	
	/**
	 * Get available width for child components (override in containers)
	 */
	public function getAvailableWidth(): int
	{
		return $this->getWidth();
	}
	
	/**
	 * Get available height per child component (override in containers)
	 */
	public function getAvailableHeightPerChild(): int
	{
		$childCount = $this->children()->count();
		if ($childCount === 0) {
			return $this->getHeight();
		}
		
		return (int) floor($this->getHeight() / $childCount);
	}

//    abstract public function render(): string;

	// =================== FOCUS EVENT HANDLERS ===================

	protected array $onEnter = [];
	protected array $onLeave = [];
	protected array $onActivate = [];
	protected array $onClick = [];
	protected bool $isFocusable = false;

	/**
	 * Make component focusable
	 */
	public function focusable(bool $focusable = true): self
	{
		$this->isFocusable = $focusable;
		return $this;
	}

	/**
	 * Check if component is focusable
	 */
	public function canFocus(): bool
	{
		return $this->isFocusable;
	}

	/**
	 * Register focus enter event handler
	 */
	public function onEnter(callable $handler): self
	{
		$this->onEnter[] = $handler;
		return $this;
	}

	/**
	 * Register focus leave event handler
	 */
	public function onLeave(callable $handler): self
	{
		$this->onLeave[] = $handler;
		return $this;
	}

	/**
	 * Register activate event handler (Enter key on focused component)
	 */
	public function onActivate(callable $handler): self
	{
		$this->onActivate[] = $handler;
		return $this;
	}

	/**
	 * Register click event handler
	 */
	public function onClick(callable $handler): self
	{
		$this->onClick[] = $handler;
		return $this;
	}

	/**
	 * Dispatch focus enter event to handlers
	 */
	public function dispatchEnterEvent(\Crumbls\Tui\Events\FocusEnterEvent $event): bool
	{
		foreach ($this->onEnter as $handler) {
			$result = $handler($event, $this);
			if ($result === true) {
				return true; // Event handled
			}
		}
		return false; // Event not handled, continue bubbling
	}

	/**
	 * Dispatch focus leave event to handlers
	 */
	public function dispatchLeaveEvent(\Crumbls\Tui\Events\FocusLeaveEvent $event): bool
	{
		foreach ($this->onLeave as $handler) {
			$result = $handler($event, $this);
			if ($result === true) {
				return true; // Event handled
			}
		}
		return false; // Event not handled, continue bubbling
	}

	/**
	 * Dispatch activate event to handlers
	 */
	public function dispatchActivateEvent(\Crumbls\Tui\Events\ActivateEvent $event): bool
	{
		foreach ($this->onActivate as $handler) {
			$result = $handler($event, $this);
			if ($result === true) {
				return true; // Event handled
			}
		}
		return false; // Event not handled, continue bubbling
	}

	/**
	 * Dispatch click event to handlers
	 */
	public function dispatchClickEvent(\Crumbls\Tui\Events\MouseEvent $event): bool
	{
		foreach ($this->onClick as $handler) {
			$result = $handler($event, $this);
			if ($result === true) {
				return true; // Event handled
			}
		}
		return false; // Event not handled, continue bubbling
	}

	// =================== RENDERING ===================

	public function render() : string {
		$padding = str_repeat('-', $this->depth * 2);
		$ret = [$padding . $this->getTitle().' - '.$this->getDepth().' - '.$this->getId().' - '.$this->parent?->getId()];
		foreach($this->children() as $child) {
			$ret[] = $child->render();
		}
		return implode("\n", $ret);
//		dd($this->children());
	}
}