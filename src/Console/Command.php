<?php

namespace Crumbls\Tui\Console;

use Crumbls\Tui\Contracts\EventBusContract;
use Crumbls\Tui\Contracts\InputBusContract;
use Crumbls\Tui\Events\MouseEvent;
use Crumbls\Tui\Events\ResizeEvent;
use Crumbls\Tui\Terminal\EventBus;
use Crumbls\Tui\Terminal\InputBus;
use Crumbls\Tui\Terminal\Renderer;
use Crumbls\Tui\Terminal\Size;
use Crumbls\Tui\Terminal\FocusBus;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Console\Command as BaseCommand;
use Illuminate\Console\View\Components\Factory;
use Illuminate\Console\OutputStyle;

abstract class Command extends BaseCommand {
	protected mixed $app;
	protected EventBusContract $_eventBus;
	protected InputBusContract $_inputBus;
	protected mixed $_cachedLayout = null;
	protected ?Size $_lastTerminalSize = null;
	protected ?FocusBus $_focusBus = null;
//	protected Renderer $_renderer;

	abstract public function getLayout(): mixed;
	
	public function handleKey($key) {
		// Try legacy focus bus first (for tabs and other SelectableInterface components)
		if ($this->getFocusBus()->handleKeyInput($key)) {
			return; // Event was handled by focused component
		}
		
		// Handle Tab navigation (new component focus system) - only if legacy didn't handle it
		if ($key === "\t") { // Tab key
			if ($this->getFocusBus()->focusNextComponent()) {
				return; // Focus navigation handled
			}
		}
		
		// Handle Shift+Tab navigation  
		if ($key === "\033[Z") { // Shift+Tab key
			if ($this->getFocusBus()->focusPreviousComponent()) {
				return; // Focus navigation handled
			}
		}
		
		// Handle Enter key activation
		if ($key === "\r" || $key === "\n") { // Enter key
			if ($this->getFocusBus()->activateFocusedComponent('enter')) {
				return; // Activation handled
			}
		}
		
		// Handle Space key activation (alternative activation)
		if ($key === ' ') { // Space key
			if ($this->getFocusBus()->activateFocusedComponent('space')) {
				return; // Activation handled
			}
		}
		
		// Default implementation - subclasses can override for global key handling
	}
	
	public function handleMouse(MouseEvent $event) {
		// Try focus bus first for proper event bubbling
		if ($this->getFocusBus()->handleMouseClick($event)) {
			return; // Event was handled by clicked component
		}
		
		// Default implementation - subclasses can override for global mouse handling
	}

	/**
	 * Create a new console command instance.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	public function handle() {
		// Build component tree
		try {
			$this->runInputLoop(
				onInput: fn($key) => $this->handleKey($key),
				onMouse: fn(MouseEvent $event) => $this->handleMouse($event),
				onResize: fn(ResizeEvent $event) => $this->handleResize($event)
			);
		} catch (\Throwable $e) {
			dd($e);
		} finally {
			$this->cleanupTui();
		}
	}

	/**
	 * Run the console command.
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @param  \Symfony\Component\Console\Output\OutputInterface  $output
	 * @return int
	 */
	#[\Override]
	public function run(InputInterface $input, OutputInterface $output): int
	{
		$this->output = $output instanceof OutputStyle ? $output : $this->laravel->make(
			OutputStyle::class, ['input' => $input, 'output' => $output]
		);

		$this->components = $this->laravel->make(Factory::class, ['output' => $this->output]);

		$this->configurePrompts($input);

		try {
			return parent::run(
				$this->input = $input, $this->output
			);
		} finally {
			$this->untrap();
		}
	}

	/**
	 * Execute the console command.
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @param  \Symfony\Component\Console\Output\OutputInterface  $output
	 */
	#[\Override]
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		if ($this instanceof Isolatable && $this->option('isolated') !== false &&
			! $this->commandIsolationMutex()->create($this)) {
			$this->comment(sprintf(
				'The [%s] command is already running.', $this->getName()
			));

			return (int) (is_numeric($this->option('isolated'))
				? $this->option('isolated')
				: $this->isolatedExitCode);
		}

		$method = method_exists($this, 'handle') ? 'handle' : '__invoke';

		try {
			return (int) $this->laravel->call([$this, $method]);
		} catch (ManuallyFailedException $e) {
			$this->components->error($e->getMessage());

			return static::FAILURE;
		} finally {
			if ($this instanceof Isolatable && $this->option('isolated') !== false) {
				$this->commandIsolationMutex()->forget($this);
			}
		}
	}

	public function getEventBus() : EventBusContract {
		if (!isset($this->_eventBus)) {
			$eventBus = new EventBus();
			$this->setEventBus($eventBus);
		}
		return $this->_eventBus;
	}

	public function setEventBus(EventBusContract $eventBus) : self{
		$register = false;

		if (!isset($this->_eventBus)) {
			$register = true;
		} else if ($this->_eventBus !== $eventBus) {
			$register = true;
		}

		$this->_eventBus = $eventBus;

		if ($register) {
			/**
			 * Register our event bus?
			 */
		}

		return $this;
	}

	public function getInputBus() : InputBusContract {
		if (!isset($this->_inputBus)) {
			$inputBus = new InputBus();
			$this->setInputBus($inputBus);
		}
		return $this->_inputBus;
	}

	public function setInputBus(InputBusContract $inputBus) : self{
		$register = false;

		if (!isset($this->_inputBus)) {
			$register = true;
		} else if ($this->_inputBus !== $inputBus) {
			$register = true;
		}

		$this->_inputBus = $inputBus;

		if ($register) {
			/**
			 * Register our input bus
			 */
		}

		return $this;
	}

	/**
	 * Get the focus bus instance
	 */
	public function getFocusBus(): FocusBus
	{
		if ($this->_focusBus === null) {
			$this->_focusBus = new FocusBus();
		}
		return $this->_focusBus;
	}

	/**
	 * Get cached layout (built only once, content updated as needed)
	 */
	protected function getCachedLayout(): mixed
	{
		if ($this->_cachedLayout === null) {
			$this->_cachedLayout = $this->getLayout();
			// Build the tree immediately after creation and cache the result
			$this->_cachedLayout::rebuildTree();
			
			// Register the root layout with focus bus
			$this->getFocusBus()->registerRoot($this->_cachedLayout);
		} else {
			// Layout exists, but content might need updating
			$this->updateLayoutContent();
		}
		return $this->_cachedLayout;
	}
	
	/**
	 * Update layout content (can be overridden by subclasses)
	 */
	protected function updateLayoutContent(): void
	{
		// Default implementation does nothing
		// Subclasses can override this to update dynamic content
	}

	/**
	 * Invalidate the cached layout (forces rebuild on next render)
	 */
	protected function invalidateLayout(): void
	{
		$this->_cachedLayout = null;
	}

	/**
	 * Clear the screen for clean rendering
	 */
	protected function clearScreen(): void
	{
		// Move cursor to top-left and clear screen
		echo "\033[2J\033[H";
	}

	/**
	 * Initialize terminal for TUI rendering
	 */
	protected function initTui(): void
	{
		// Hide cursor and enable mouse reporting
		echo "\033[?25l\033[?1000h\033[?1006h";
	}

	/**
	 * Clean up terminal state
	 */
	protected function cleanupTui(): void
	{
		// Show cursor and disable mouse reporting
		echo "\033[?25h\033[?1000l\033[?1006l";
		// Clear screen and move cursor to bottom
		echo "\033[2J\033[H";
	}

	/**
	 * Render the application
	 */
	public function render(): void
	{
		$this->clearScreen();
		echo $this->getCachedLayout()->render();
	}

	/**
	 * Run an input loop for TUI applications
	 */
	protected function runInputLoop(callable $render = null, callable $onInput = null, callable $onMouse = null, callable $onResize = null): void
	{
		// Initialize TUI
		$this->initTui();
		
		// Store initial terminal size
		$this->_lastTerminalSize = Size::current();
		
		$inputBus = $this->getInputBus();
		$inputBus->startListening();

		// Register mouse event handler
		if ($onMouse) {
			$inputBus->onMouseEvent($onMouse);
		}

		// Render callback is now required for TUI apps
		if (!$render) {
			$render = function() {
				$this->render();
			};
		}

		try {
			// Initial render
			$render();
			
			while ($inputBus->isListening()) {
				// Check for terminal resize
				$currentSize = Size::detect();
				if (!$this->_lastTerminalSize->equals($currentSize)) {
					$resizeEvent = new ResizeEvent($this->_lastTerminalSize, $currentSize);
					$this->_lastTerminalSize = $currentSize;
					
					// Handle resize event
					if ($onResize) {
						$onResize($resizeEvent);
					} else {
						$this->handleResize($resizeEvent);
					}
					
					// Re-render after resize
					$render();
				}
				
				// Check for input
				if ($inputBus->hasInput()) {
					$key = $inputBus->readKey();
					
					if ($key !== null) {
						// Default quit on 'q'
						if ($key === 'q') {
							$inputBus->stopListening();
							break;
						}

						// Call custom input handler if provided
						if ($onInput) {
							$onInput($key);
						}
						
						// Re-render after input
						$render();
					}
				}

				// Small delay to prevent high CPU usage
				usleep(16667); // ~60 FPS
			}
		} finally {
			$inputBus->stopListening();
		}
	}
	
	/**
	 * Handle terminal resize events (can be overridden by subclasses)
	 */
	protected function handleResize(ResizeEvent $event): void
	{
		// Default implementation: refresh terminal size cache
		Size::refresh();
		
		// Invalidate layout cache so it picks up new dimensions
		$this->invalidateLayout();
	}

	public function renderUI() {
//		$this->info(__LINE__);
	}
}