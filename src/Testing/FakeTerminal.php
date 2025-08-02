<?php

declare(strict_types=1);

namespace Crumbls\Tui\Testing;

use Crumbls\Tui\Contracts\TerminalInterface;
use Crumbls\Tui\Events\TuiEvent;

class FakeTerminal implements TerminalInterface
{
    protected array $written = [];
    protected array $events = [];
    protected array $size = [80, 24];
    protected bool $rawMode = false;

    public function write(string $content): static
    {
        $this->written[] = $content;

        return $this;
    }

    public function clear(): static
    {
        $this->written = [];

        return $this;
    }

    public function flush(): static
    {
        return $this;
    }

    public function enableRawMode(): static
    {
        $this->rawMode = true;

        return $this;
    }

    public function disableRawMode(): static
    {
        $this->rawMode = false;

        return $this;
    }

    public function readEvent(): ?TuiEvent
    {
        return array_shift($this->events);
    }

    public function getSize(): array
    {
        return $this->size;
    }

    public function getWritten(): array
    {
        return $this->written;
    }

    public function getLastWritten(): ?string
    {
        return end($this->written) ?: null;
    }

    public function getAllWritten(): string
    {
        return implode('', $this->written);
    }

    public function pushEvent(TuiEvent $event): static
    {
        $this->events[] = $event;

        return $this;
    }

    public function setSize(int $width, int $height): static
    {
        $this->size = [$width, $height];

        return $this;
    }

    public function isRawModeEnabled(): bool
    {
        return $this->rawMode;
    }

    public function assertWritten(string $expected): static
    {
        $written = $this->getAllWritten();
        
        if (!str_contains($written, $expected)) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                "Expected terminal to contain '{$expected}', but got: {$written}"
            );
        }

        return $this;
    }

    public function assertNotWritten(string $unexpected): static
    {
        $written = $this->getAllWritten();
        
        if (str_contains($written, $unexpected)) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                "Expected terminal to not contain '{$unexpected}', but it was found in: {$written}"
            );
        }

        return $this;
    }
}