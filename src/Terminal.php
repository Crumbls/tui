<?php

declare(strict_types=1);

namespace Crumbls\Tui;

use Crumbls\Tui\Contracts\TerminalInterface;
use Crumbls\Tui\Events\TuiEvent;
use Crumbls\Tui\Support\TerminalSize;

class Terminal implements TerminalInterface
{
    protected bool $isRawMode = false;
    protected array $buffer = [];

    public function __construct(
        protected ?TerminalInterface $backend = null
    ) {
        $this->backend = $backend ?? new Terminal\AnsiTerminal();
    }

    public static function make(?TerminalInterface $backend = null): static
    {
        return new static($backend);
    }

    public function write(string $content): static
    {
        $this->buffer[] = $content;

        return $this;
    }

    public function clear(): static
    {
        $this->buffer = [];
        
        return $this->write("\033[2J\033[H");
    }

    public function flush(): static
    {
        if (empty($this->buffer)) {
            return $this;
        }

        $content = implode('', $this->buffer);
        $this->buffer = [];

        $this->backend->write($content);

        return $this;
    }

    public function enableRawMode(): static
    {
        if (!$this->isRawMode) {
            $this->backend->enableRawMode();
            $this->isRawMode = true;
        }

        return $this;
    }

    public function disableRawMode(): static
    {
        if ($this->isRawMode) {
            $this->backend->disableRawMode();
            $this->isRawMode = false;
        }

        return $this;
    }

    public function readEvent(): ?TuiEvent
    {
        return $this->backend->readEvent();
    }

    public function getSize(): array
    {
        return $this->backend->getSize();
    }

    public function isRawMode(): bool
    {
        return $this->isRawMode;
    }
}