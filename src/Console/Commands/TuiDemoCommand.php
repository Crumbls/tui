<?php

declare(strict_types=1);

namespace Crumbls\Tui\Console\Commands;

use Crumbls\Tui\Components\Layout;
use Crumbls\Tui\Components\Panel;
use Crumbls\Tui\Components\Paragraph;
use Crumbls\Tui\Components\Row;
use Crumbls\Tui\Components\Tabs;
use Crumbls\Tui\Components\Tab;
use Crumbls\Tui\Events\MouseEvent;
use Crumbls\Tui\Events\ResizeEvent;
use Crumbls\Tui\Console\Command;

class TuiDemoCommand extends Command
{
    protected $signature = 'tui:demo';
    protected $description = 'Modern TUI demo showcasing the layout system and components';

    protected ?Layout $layout = null;
    protected ?Tabs $tabs = null;
    protected ?Panel $mainContent = null;

	public function getLayout() : Layout {
		// Create layout structure only once
		if ($this->layout === null) {
			$this->initializeLayoutStructure();
		}
		
		return $this->layout;
	}
	
	protected function initializeLayoutStructure(): void
	{
		// Create the fixed layout structure once
		$this->layout = Layout::make('TUI Demo Application')
			->title('TUI Demo v2.0');

		// Create tabs with event handling and custom key bindings
		$this->tabs = Tabs::make()
			->with(
				Tab::make('Welcome')->keyBinding('ctrl+1'),
				Tab::make('Components')->keyBinding('ctrl+2')
			)
			->height(3) // Set explicit height so main content gets remaining space
			->selectable(true)
			->onTabChange(function(int $oldTab, int $newTab, Tabs $tabs) {
				$this->updateMainContentForTab($newTab);
			});

		$this->mainContent = Panel::make('MainContent');
			
		$this->layout->with($this->tabs, $this->mainContent);
		
		// Set initial focus on tabs for proper event handling
		$this->getFocusBus()->setFocus($this->tabs);
		
		// Initialize with first tab content
		$this->updateMainContentForTab(0);
	}
	
	/**
	 * Update main content based on active tab
	 */
	protected function updateMainContentForTab(int $tabIndex): void
	{
		if ($this->mainContent === null) {
			return;
		}
		
		// Clear current content
		$this->mainContent->clearChildren();
		
		// Add content based on tab
		switch ($tabIndex) {
			case 0:
				$this->mainContent
					->title('Welcome')
					->with($this->createWelcomeContent());
				break;
			case 1:
				$this->mainContent
					->title('Components')
					->with($this->createComponentsContent());
				break;
		}
	}
	
	/**
	 * Override base class method - no longer needed with event-driven tabs
	 */
	protected function updateLayoutContent(): void
	{
		// Content updates are now handled by tab change events
	}


	protected function createWelcomeContent(): Panel
	{
		return Panel::make('Welcome')
			->title('Welcome to TUI Demo')
			->with(
				Paragraph::make('intro')
					->title('Press 1-7 to navigate tabs • Press q to quit • Mouse clicks work too!'),
				Paragraph::make('intro')
					->title('Press 1-7 to navigate tabs • Press q to quit • Mouse clicks work too!')
			);
	}

	protected function createComponentsContent(): Panel  
	{
		return Panel::make('Components')
//			->title('Modern Component Architecture')
			->noTitle()
			->with(
				Row::make()->with(
					Panel::make('Features')
						->title('Features')
						->with(
							Paragraph::make('feature-list')
								->content('• DOM-like event system • Focus management • Fluent API • Laravel patterns')
						),
					Panel::make('Example')
						->title('qCode Example')
						->noBorder()
						->with(
							Paragraph::make('code')
								->content('Block::card()->title("My Card")->focusable()->onClick($handler)')
						)
				)
			);
	}

	public function handleKey($key) {
		// Call parent to handle focus bus event bubbling first
		parent::handleKey($key);
		
		// Handle global keys that weren't handled by focused components
		// (like 'q' for quit is already handled by base input loop)
	}

	public function handleMouse(MouseEvent $event) {
		// Call parent to handle focus bus event bubbling first
		parent::handleMouse($event);
		
		// Handle global mouse events that weren't handled by focused components
		if ($event->isClick() && $event->clickedComponent) {
			$this->info("Clicked on component: " . $event->clickedComponent->getId());
			$this->info("At coordinates: ({$event->x}, {$event->y}, {$event->clickedComponent->getDepth()})");
		}
	}

	public function handleResize(ResizeEvent $event): void
	{
		// Call parent to handle the basic resize (invalidate layout cache)
		parent::handleResize($event);
		
		// Auto-sizing handles terminal resize automatically
		// No need to manually recalculate panel sizes
		// Components will auto-fill their containers on next render
	}
}
