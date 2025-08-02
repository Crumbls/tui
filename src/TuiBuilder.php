<?php

declare(strict_types=1);

namespace Crumbls\Tui;

use Crumbls\Tui\Contracts\TerminalInterface;
use Crumbls\Tui\Contracts\WidgetInterface;
use Crumbls\Tui\Layout\Layout;
use Illuminate\Support\Collection;

class TuiBuilder
{
    protected Collection $widgets;
    protected ?Layout $layout = null;

    public function __construct(
        protected TerminalInterface $terminal
    ) {
        $this->widgets = collect();
    }

    public static function make(TerminalInterface $terminal): static
    {
        return new static($terminal);
    }

    public function layout(Layout $layout): static
    {
        $this->layout = $layout;

        return $this;
    }

    public function add(WidgetInterface $widget): static
    {
        $this->widgets->push($widget);

        return $this;
    }

    public function widget(WidgetInterface $widget): static
    {
        return $this->add($widget);
    }

    public function widgets(array $widgets): static
    {
        foreach ($widgets as $widget) {
            $this->add($widget);
        }

        return $this;
    }

    public function render(): string
    {
        $output = '';

        foreach ($this->widgets as $widget) {
            $output .= $widget->render();
        }

        return $output;
    }

    public function display(): static
    {
        // Clear screen and move cursor to top-left
        $this->terminal->clear();
        $this->terminal->write($this->render())->flush();

        return $this;
    }

    public function run(): void
    {
        $this->terminal->enableRawMode();
        
        try {
            $this->display();
            $this->startEventLoop();
        } finally {
            $this->terminal->disableRawMode();
        }
    }

    protected function startEventLoop(): void
    {
        while (true) {
            $key = $this->readKey();
            
            if ($key === null) {
                usleep(50000); // 50ms delay to prevent CPU spinning
                continue;
            }

            if ($this->shouldExit($key)) {
                break;
            }

            $this->handleKeyPress($key);
            $this->display(); // Refresh display after handling key
        }
    }

    protected function readKey(): ?string
    {
        // Read a single character from stdin
        if (function_exists('stream_select')) {
            $read = [STDIN];
            $write = null;
            $except = null;
            
            if (stream_select($read, $write, $except, 0, 100000) > 0) {
                return fread(STDIN, 1);
            }
        }
        
        return null;
    }

    protected function shouldExit(string $key): bool
    {
        // Exit on 'q', 'Q', or Ctrl+C
        return in_array($key, ['q', 'Q', "\x03"]);
    }

    protected function handleKeyPress(string $key): void
    {
        // Handle navigation keys for widgets
        foreach ($this->widgets as $widget) {
            if (method_exists($widget, 'handleKey')) {
                $widget->handleKey($key);
            }
        }
    }

    public function getTerminal(): TerminalInterface
    {
        return $this->terminal;
    }

    public function getWidgets(): Collection
    {
        return $this->widgets;
    }
}