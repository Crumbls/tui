<?php

declare(strict_types=1);

namespace Crumbls\Tui;

use Crumbls\Tui\Contracts\TerminalInterface;
use Crumbls\Tui\Contracts\ErrorHandlerInterface;
use Crumbls\Tui\Terminal\Terminal;
use Crumbls\Tui\Terminal\SafeTerminal;
use Crumbls\Tui\ErrorHandling\ErrorHandler;
use Crumbls\Tui\Events\EventBus;
use Crumbls\Tui\Input\InputHandler;
use Crumbls\Tui\Rendering\SimpleRenderer;

/**
 * Factory for creating bulletproof TUI components with error handling.
 */
class TuiFactory
{
    private ?ErrorHandlerInterface $errorHandler = null;
    private ?TerminalInterface $terminal = null;

    /**
     * Create a complete TUI stack with error handling.
     */
    public static function create(): static
    {
        return new static();
    }

    /**
     * Get or create the error handler.
     */
    public function errorHandler(): ErrorHandlerInterface
    {
        if ($this->errorHandler === null) {
            // Create error handler without terminal initially
            $this->errorHandler = new ErrorHandler(null, true);
        }
        
        return $this->errorHandler;
    }

    /**
     * Get or create a bulletproof terminal.
     */
    public function terminal(): TerminalInterface
    {
        if ($this->terminal === null) {
            $rawTerminal = Terminal::make();
            $errorHandler = $this->errorHandler();
            $this->terminal = new SafeTerminal($rawTerminal, $errorHandler);
        }
        
        return $this->terminal;
    }

    /**
     * Create an event bus.
     */
    public function eventBus(): EventBus
    {
        return new EventBus();
    }

    /**
     * Create an input handler with error handling.
     */
    public function inputHandler(?EventBus $eventBus = null): InputHandler
    {
        $eventBus = $eventBus ?? $this->eventBus();
        $handler = new InputHandler($this->terminal(), $eventBus);
        
        // Register input error handling
        $this->errorHandler()->registerCleanupHandler(function () use ($handler) {
            // Future: Could add input cleanup if needed
        });
        
        return $handler;
    }

    /**
     * Create a renderer with error handling.
     */
    public function renderer(): SimpleRenderer
    {
        return new SimpleRenderer($this->terminal());
    }

    /**
     * Create a main loop with all components integrated and error handling.
     */
    public function mainLoop(bool $enableMouse = false): MainLoop
    {
        $terminal = $this->terminal();
        $eventBus = $this->eventBus();
        $inputHandler = $this->inputHandler($eventBus);
        
        if ($enableMouse) {
            $inputHandler->setMouseEnabled(true);
        }
        
        $loop = MainLoop::create($terminal, $eventBus, $inputHandler);
        
        // Register loop cleanup
        $this->errorHandler()->registerCleanupHandler(function () use ($loop) {
            try {
                $loop->stop();
            } catch (\Exception $e) {
                // Ignore cleanup errors
            }
        });
        
        return $loop;
    }

    /**
     * Create a bulletproof demo setup.
     */
    public function demo(): array
    {
        $terminal = $this->terminal();
        $eventBus = $this->eventBus();
        $inputHandler = $this->inputHandler($eventBus);
        $inputHandler->setMouseEnabled(true);
        $renderer = $this->renderer();
        $errorHandler = $this->errorHandler();
        
        $loop = MainLoop::create($terminal, $eventBus, $inputHandler)
            ->setTickRate(10)
            ->setIdleTickRate(2)
            ->setIdleThreshold(3);
        
        return [
            'terminal' => $terminal,
            'eventBus' => $eventBus,
            'inputHandler' => $inputHandler,
            'renderer' => $renderer,
            'errorHandler' => $errorHandler,
            'loop' => $loop,
        ];
    }
}