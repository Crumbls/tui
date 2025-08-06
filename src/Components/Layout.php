<?php

namespace Crumbls\Tui\Components;

use Crumbls\Tui\Terminal\Size;
use Crumbls\Tui\Components\Concerns\HasBorders;

class Layout extends Component
{
    use HasBorders;
	
    protected int $width = 80;
    protected int $height = 24;
    protected bool $autoSize = true;
    protected ?Size $terminalSize = null;

    public static function make(string $id = null): self
    {
        $layout = new self($id);
        $layout->initializeBorders();
        $layout->updateTerminalSize();
        return $layout;
	}
	
	public function updateTerminalSize(): self
	{
		if ($this->autoSize) {
			$this->terminalSize = Size::current();
			$this->width = $this->terminalSize->getWidth();
			// Reserve one line for the prompt after TUI exits
			$this->height = $this->terminalSize->getHeight() - 1;
		}
		return $this;
	}
	
	public function size(int $width, int $height): self
	{
		$this->width = $width;
		$this->height = $height;
		$this->autoSize = false;
		return $this;
	}
	
	public function autoSize(bool $autoSize = true): self
	{
		$this->autoSize = $autoSize;
		if ($autoSize) {
			$this->updateTerminalSize();
		}
		return $this;
	}
	
	public function getWidth(): int
	{
		return $this->width;
	}
	
	public function getHeight(): int
	{
		return $this->height;
	}
	
	/**
	 * Get available width for child components (account for borders)
	 */
	public function getAvailableWidth(): int
	{
		return $this->getContentWidth($this->width);
	}
	
	/**
	 * Get available height per child component (account for borders and explicit sizes)
	 */
	public function getAvailableHeightPerChild(): int
	{
		$contentHeight = $this->getContentHeight($this->height);
		$children = $this->children();
		
		if ($children->isEmpty()) {
			return $contentHeight;
		}
		
		// Calculate space used by children with explicit heights
		$usedHeight = 0;
		$autoSizeCount = 0;
		
		foreach ($children as $child) {
			if (property_exists($child, 'explicitHeight') && $child->explicitHeight) {
				$usedHeight += $child->height;
			} else {
				$autoSizeCount++;
			}
		}
		
		// Distribute remaining space among auto-sizing children
		if ($autoSizeCount === 0) {
			return 0;
		}
		
		$remainingHeight = max(0, $contentHeight - $usedHeight);
		return (int) floor($remainingHeight / $autoSizeCount);
	}
	public function render() : string {
		// Update terminal size if auto-sizing is enabled
		if ($this->autoSize) {
			$newSize = Size::detect();
			if ($this->terminalSize === null || !$this->terminalSize->equals($newSize)) {
				$this->terminalSize = $newSize;
				$this->width = $newSize->getWidth();
				// Reserve one line for the prompt after TUI exits
				$this->height = $newSize->getHeight() - 1;
			}
		}
		
		return $this->renderFullWindow();
	}
	
	protected function renderFullWindow(): string
	{
		$title = $this->getTitle() ?: 'TUI Application';
		$contentHeight = $this->getContentHeight($this->height);
		$contentArea = $this->renderContentArea($contentHeight);
		$contentLines = explode("\n", $contentArea);
		
		return $this->renderBorderedBox($this->width, $this->height, $contentLines, $title);
	}
	
	protected function renderContentArea(int $availableHeight): string
	{
		if ($this->children()->isEmpty()) {
			return str_repeat("\n", $availableHeight);
		}
		
		$childrenOutput = [];
		foreach ($this->children() as $child) {
			$childrenOutput[] = $child->render();
		}
		
		$content = implode("\n", $childrenOutput);
		$lines = explode("\n", $content);
		
		// Pad or truncate to fit available height
		$result = [];
		for ($i = 0; $i < $availableHeight; $i++) {
			$result[] = $lines[$i] ?? '';
		}
		
		return implode("\n", $result);
	}
}