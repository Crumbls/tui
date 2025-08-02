<?php

declare(strict_types=1);

namespace Crumbls\Tui;

use Crumbls\Tui\Contracts\WidgetInterface;
use Crumbls\Tui\Events\KeyEvent;
use Crumbls\Tui\Components\TabContainer;

class App
{
    protected ?WidgetInterface $root = null;
    protected bool $running = false;
    protected array $keyHandlers = [];
    protected int $width = 80;
    protected int $height = 24;
    protected bool $autoTabNavigation = true;

    public static function create(): static
    {
        return new static();
    }

    public function root(WidgetInterface $widget): static
    {
        $this->root = $widget;
        return $this;
    }

    public function size(int $width, int $height): static
    {
        $this->width = $width;
        $this->height = $height;
        return $this;
    }

    public function disableAutoTabNavigation(): static
    {
        $this->autoTabNavigation = false;
        return $this;
    }

    public function onKey(string $key, callable $handler): static
    {
        $this->keyHandlers[$key] = $handler;
        return $this;
    }

    public function onArrowLeft(callable $handler): static
    {
        return $this->onKey('left', $handler);
    }

    public function onArrowRight(callable $handler): static
    {
        return $this->onKey('right', $handler);
    }

    public function onArrowUp(callable $handler): static
    {
        return $this->onKey('up', $handler);
    }

    public function onArrowDown(callable $handler): static
    {
        return $this->onKey('down', $handler);
    }

    public function onQuit(callable $handler): static
    {
        return $this->onKey('q', $handler);
    }

    public function run(): void
    {
        if (!$this->root) {
            throw new \RuntimeException('No root widget set. Call root() first.');
        }

        if (!function_exists('posix_isatty') || !posix_isatty(STDOUT)) {
            throw new \RuntimeException('This application must be run in a TTY terminal.');
        }

        $this->enterRawMode();
        $this->running = true;

        try {
            $this->draw();

            while ($this->running) {
                $key = $this->readKey();
                
                if ($key) {
                    $this->handleKey($key);
                }
                
                usleep(50_000); // 50ms delay
            }
        } finally {
            $this->exitRawMode();
        }
    }

    public function quit(): void
    {
        $this->running = false;
    }

    public function draw(): void
    {
        if (!$this->root) {
            return;
        }

        // Update terminal size
        $this->width = (int) exec('tput cols');
        $this->height = (int) exec('tput lines');

        // Set size on root widget if it supports it
        if (method_exists($this->root, 'setRegion')) {
            $this->root->setRegion($this->width, $this->height);
        }

        echo "\033[H\033[2J"; // Clear screen and move cursor to top
        echo $this->root->render();
    }

    public function getSize(): array
    {
        return [$this->width, $this->height];
    }

    protected function enterRawMode(): void
    {
        echo "\033[?1049h"; // Enter alternate screen buffer
        echo "\033[3J";     // Clear scrollback buffer
        system('stty cbreak -echo'); // Enable raw mode
    }

    protected function exitRawMode(): void
    {
        echo "\033[?1049l"; // Exit alternate screen buffer
        system('stty -cbreak echo'); // Restore normal mode
    }

    protected function readKey(): ?string
    {
        stream_set_blocking(STDIN, false);
        $char = fgetc(STDIN);
        
        if ($char === false) {
            return null;
        }

        // Handle escape sequences (arrow keys)
        if ($char === "\033") {
            $next1 = fgetc(STDIN);
            $next2 = fgetc(STDIN);
            
            if ($next1 === '[') {
                return match ($next2) {
                    'A' => 'up',
                    'B' => 'down', 
                    'C' => 'right',
                    'D' => 'left',
                    default => null,
                };
            }
        }

        return $char;
    }

    protected function handleKey(string $key): void
    {
        // Handle quit key automatically
        if ($key === 'q' || $key === 'Q') {
            if (isset($this->keyHandlers['q'])) {
                $this->keyHandlers['q']();
            } else {
                $this->quit(); // Default quit behavior
            }
            return;
        }

        // Auto-handle tab navigation if enabled and root is TabContainer
        if ($this->autoTabNavigation && $this->root instanceof TabContainer) {
            if ($key === 'left') {
                $this->root->previousTab();
                $this->draw();
                return;
            } elseif ($key === 'right') {
                $this->root->nextTab();
                $this->draw();
                return;
            }
        }

        // Handle registered key handlers
        if (isset($this->keyHandlers[$key])) {
            $this->keyHandlers[$key]();
            $this->draw(); // Auto-redraw after key handler
        }
    }
}