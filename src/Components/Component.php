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