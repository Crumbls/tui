<?php

namespace Crumbls\Tui\Contracts;

use Crumbls\Tui\Components\Component;
use Crumbls\Tui\Components\Layout;

interface RendererContract
{
    /**
     * Set the layout for rendering
     */
    public function setLayout(Layout $layout): self;

    /**
     * Get the terminal instance
     */
    public function getTerminal(): TerminalContract;

    /**
     * Get the screen instance  
     */
    public function getScreen(): ScreenContract;

    /**
     * Set the input bus for event handling
     */
    public function setInputBus(InputBusContract $inputBus): self;

    /**
     * Render a component to the terminal
     */
    public function render(Component $rootComponent): self;

    /**
     * Initialize the renderer
     */
    public function initialize(): self;

    /**
     * Clean up renderer resources
     */
    public function cleanup(): self;
}