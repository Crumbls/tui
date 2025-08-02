<?php

declare(strict_types=1);

namespace Crumbls\Tui;

use Crumbls\Tui\Contracts\LoopInterface;
use Crumbls\Tui\Contracts\TerminalInterface;
use Crumbls\Tui\Contracts\InputHandlerInterface;
use Crumbls\Tui\Contracts\EventBusInterface;

class MainLoop implements LoopInterface
{
    protected $onTick = null;

    /**
     * Create a fully configured MainLoop with all Phase 1 components.
     */
    public static function create(
        ?TerminalInterface $terminal = null,
        ?EventBusInterface $eventBus = null,
        ?InputHandlerInterface $inputHandler = null
    ): static {
        $loop = new static();
        
        if ($terminal) {
            $loop->setTerminal($terminal);
        }
        
        if ($eventBus) {
            $loop->setEventBus($eventBus);
        }
        
        if ($inputHandler) {
            $loop->setInputHandler($inputHandler);
        }
        
        return $loop;
    }
    protected $onRender = null;
    protected $onInput = null;
    protected int $tickRate = 30;
    protected bool $running = false;
    protected float $lastActivity = 0.0;
    protected int $idleThreshold = 5; // seconds
    protected int $idleTickRate = 5; // Hz when idle
    protected bool $isIdle = false;
    protected ?EventBusInterface $eventBus = null;
    protected ?TerminalInterface $terminal = null;
    protected ?InputHandlerInterface $inputHandler = null;

    public function onTick(callable $callback): static
    {
        $this->onTick = $callback;
        return $this;
    }

    public function onRender(callable $callback): static
    {
        $this->onRender = $callback;
        return $this;
    }

    public function onInput(callable $callback): static
    {
        $this->onInput = $callback;
        return $this;
    }

    public function setTickRate(int $hz): static
    {
        $this->tickRate = $hz > 0 ? $hz : 30;
        return $this;
    }

    public function setEventBus(EventBusInterface $eventBus): static
    {
        $this->eventBus = $eventBus;
        $eventBus->listen('UserActivityEvent', function () {
            $this->lastActivity = microtime(true);
            $this->isIdle = false;
        });
        return $this;
    }

    public function setTerminal(TerminalInterface $terminal): static
    {
        $this->terminal = $terminal;
        return $this;
    }

    public function setInputHandler(InputHandlerInterface $inputHandler): static
    {
        $this->inputHandler = $inputHandler;
        return $this;
    }

    public function setIdleThreshold(int $seconds): static
    {
        $this->idleThreshold = $seconds;
        return $this;
    }

    public function setIdleTickRate(int $hz): static
    {
        $this->idleTickRate = $hz;
        return $this;
    }

    public function start(): void
    {
        $this->running = true;
        $this->lastActivity = microtime(true);
        while ($this->running) {
            $now = microtime(true);
            $interval = 1 / ($this->isIdle ? $this->idleTickRate : $this->tickRate);
            if (($now - $this->lastActivity) > $this->idleThreshold) {
                $this->isIdle = true;
            }
            $start = microtime(true);

            // Tick callback
            if ($this->onTick) {
                ($this->onTick)();
            }

            // Process input using our InputHandler
            if ($this->inputHandler) {
                $hasInput = $this->inputHandler->processInput(0); // Non-blocking
                if ($hasInput && $this->onInput) {
                    ($this->onInput)();
                }
            } elseif ($this->onInput) {
                ($this->onInput)(); // Fallback for custom input handling
            }

            // Render callback
            if ($this->onRender) {
                ($this->onRender)();
            }

            // Timing control
            $elapsed = microtime(true) - $start;
            $sleep = max(0, $interval - $elapsed);
            if ($sleep > 0) {
                usleep((int)($sleep * 1_000_000));
            }
        }
    }

    public function stop(): void
    {
        $this->running = false;
    }

    public function tick(): bool
    {
        $now = microtime(true);
        if (($now - $this->lastActivity) > $this->idleThreshold) {
            $this->isIdle = true;
        } else {
            $this->isIdle = false;
        }
        if ($this->onTick) {
            ($this->onTick)();
        }
        if ($this->inputHandler) {
            $hasInput = $this->inputHandler->processInput(0);
            if ($hasInput && $this->onInput) {
                ($this->onInput)();
            }
        } elseif ($this->onInput) {
            ($this->onInput)();
        }
        if ($this->onRender) {
            ($this->onRender)();
        }
        return $this->running;
    }

    /**
     * Check if the loop is currently idle.
     */
    public function isIdle(): bool
    {
        return $this->isIdle;
    }
}
