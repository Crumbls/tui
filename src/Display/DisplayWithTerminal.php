<?php

declare(strict_types=1);

namespace Crumbls\Tui\Display;

use Crumbls\Tui\Terminal\Terminal;
use Crumbls\Tui\Widget\Widget;

/**
 * A wrapper around Display that also provides access to the Terminal.
 * This makes it easy for users to get both the display and terminal in one go.
 */
class DisplayWithTerminal
{
    public function __construct(
        private readonly Display $display,
        private readonly ?Terminal $terminal
    ) {
    }

    /**
     * Draw a widget to the display
     */
    public function draw(Widget $widget): void
    {
        $this->display->draw($widget);
    }

    /**
     * Get the terminal instance
     */
    public function getTerminal(): ?Terminal
    {
        return $this->terminal;
    }

    /**
     * Get the underlying display
     */
    public function getDisplay(): Display
    {
        return $this->display;
    }
    
    /**
     * Forward all other method calls to the underlying display
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->display->$method(...$arguments);
    }
}