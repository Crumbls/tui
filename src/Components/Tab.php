<?php

namespace Crumbls\Tui\Components;

use Crumbls\Tui\Events\MouseEvent;
use Crumbls\Tui\Contracts\SelectableInterface;
use Crumbls\Tui\Components\Concerns\Selectable;

class Tab extends Component implements SelectableInterface
{
    use Selectable;
    
    protected string $label = '';
    protected bool $active = false;
    protected ?string $keyBinding = null;
    protected array $onClick = [];
    protected array $onFocus = [];
    protected array $onBlur = [];

	public static function make(string $label = ''): self
	{
		$tab = new static();
		$tab->label = $label;
		$tab->selectable(true); // Make tabs selectable by default
		return $tab;
	}

	/**
	 * Set the tab label
	 */
	public function label(string $label): self
	{
		$this->label = $label;
		return $this;
	}

	/**
	 * Get the tab label
	 */
	public function getLabel(): string
	{
		return $this->label;
	}

	/**
	 * Set a custom key binding for this tab (e.g., 'ctrl+w', 'cmd+c', 'alt+h')
	 */
	public function keyBinding(string $keyBinding): self
	{
		$normalized = strtolower($keyBinding);
		
		// Block common system controls and reserved shortcuts
		$blockedBindings = [
			// System controls
			'alt+f4',        // Close window
			'cmd+q',         // Quit application (macOS)
			'ctrl+alt+del',  // Task manager (Windows)
			
			// Terminal controls
			'ctrl+c',        // SIGINT - interrupt process
			'ctrl+z',        // SIGTSTP - suspend process  
			'ctrl+d',        // EOF - end of file
			'ctrl+s',        // XOFF - pause terminal output
			'ctrl+q',        // XON - resume terminal output
			'ctrl+\\',       // SIGQUIT - quit with core dump
			
			// Common application shortcuts
			'ctrl+a',        // Select all
			'ctrl+x',        // Cut
			'ctrl+v',        // Paste
			'ctrl+n',        // New
			'ctrl+o',        // Open
			'ctrl+p',        // Print
			'ctrl+f',        // Find
			'ctrl+h',        // Help/Replace
			'ctrl+r',        // Refresh/Replace
			'ctrl+t',        // New tab
			'ctrl+w',        // Close tab/window
			'ctrl+y',        // Redo
			'ctrl+u',        // Underline/Clear line
			'ctrl+i',        // Italic/Tab
			'ctrl+k',        // Delete to end of line
			'ctrl+l',        // Clear screen
			'ctrl+e',        // End of line
			'ctrl+b',        // Bold/Back
			'ctrl+m',        // Enter/Return
			'ctrl+j',        // Line feed
			
			// Navigation keys that might conflict
			'tab',           // Tab navigation
			'shift+tab',     // Reverse tab
			'enter',         // Enter/Return
			'escape',        // Escape
			'space',         // Space bar
		];
		
		if (in_array($normalized, $blockedBindings)) {
			throw new \InvalidArgumentException(
				"Key binding '{$keyBinding}' is reserved for system use. " .
				"Please choose a different combination like 'ctrl+1', 'alt+a', etc."
			);
		}
		
		$this->keyBinding = $normalized;
		return $this;
	}

	/**
	 * Get the tab's key binding
	 */
	public function getKeyBinding(): ?string
	{
		return $this->keyBinding;
	}

	/**
	 * Set the active state
	 */
	public function active(bool $active = true): self
	{
		$this->active = $active;
		return $this;
	}

	/**
	 * Check if tab is active
	 */
	public function isActive(): bool
	{
		return $this->active;
	}

	/**
	 * Make tab focusable or not (alias for selectable)
	 */
	public function focusable(bool $focusable = true): self
	{
		return $this->selectable($focusable);
	}

	/**
	 * Add click event handler
	 */
	public function onClick(callable $handler): self
	{
		$this->onClick[] = $handler;
		return $this;
	}

	/**
	 * Add focus event handler
	 */
	public function onFocus(callable $handler): self
	{
		$this->onFocus[] = $handler;
		return $this;
	}

	/**
	 * Add blur event handler
	 */
	public function onBlur(callable $handler): self
	{
		$this->onBlur[] = $handler;
		return $this;
	}

	/**
	 * Handle mouse click events
	 */
	public function handleMouseClick(MouseEvent $event): bool
	{
		return $this->handleMouseInput($event);
	}
	
	/**
	 * Handle mouse input events for focus system
	 */
	public function handleMouseInput(MouseEvent $event): bool
	{
		if ($event->isClick()) {
			foreach ($this->onClick as $handler) {
				$result = $handler($event, $this);
				if ($result === true) {
					return true; // Event handled
				}
			}
		}

		return false; // Event not handled, let it bubble
	}

	/**
	 * Handle focus event
	 */
	public function handleFocus(): bool
	{
		if ($this->isSelectable()) {
			foreach ($this->onFocus as $handler) {
				$result = $handler($this);
				if ($result === true) {
					return true; // Event handled
				}
			}
		}

		return false; // Event not handled, let it bubble
	}

	/**
	 * Handle blur event
	 */
	public function handleBlur(): bool
	{
		foreach ($this->onBlur as $handler) {
			$result = $handler($this);
			if ($result === true) {
				return true; // Event handled
			}
		}

		return false; // Event not handled, let it bubble
	}

	/**
	 * Set content for the tab (children components)
	 */
	public function content(Component ...$components): self
	{
		foreach ($components as $component) {
			$this->with($component);
		}
		return $this;
	}

	/**
	 * Render the tab's content (not the tab label itself)
	 * The tab label is rendered by the parent Tabs component
	 */
	public function render(): string
	{
		// Render all child components
		$content = [];
		foreach ($this->children() as $child) {
			$content[] = $child->render();
		}

		return implode("\n", $content);
	}

	/**
	 * Create a card-style tab with border
	 */
	public static function card(string $label = ''): self
	{
		return static::make($label);
	}

	/**
	 * Create a simple tab
	 */
	public static function simple(string $label = ''): self
	{
		return static::make($label);
	}
}