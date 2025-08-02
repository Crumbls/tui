<?php

declare(strict_types=1);

namespace Crumbls\Tui\Input;

use Crumbls\Tui\Contracts\InputHandlerInterface;
use Crumbls\Tui\Contracts\TerminalInterface;
use Crumbls\Tui\Contracts\EventBusInterface;

/**
 * Fluent builder for InputHandler configuration.
 */
class InputHandlerBuilder
{
    private bool $mouseEnabled = false;
    private array $keyMappings = [];
    private array $ignoredKeys = [];
    private bool $enableRawSequences = true;
    private float $defaultTimeout = 0;

    public function __construct(
        private TerminalInterface $terminal,
        private EventBusInterface $eventBus
    ) {
    }

    public static function for(TerminalInterface $terminal, EventBusInterface $eventBus): static
    {
        return new static($terminal, $eventBus);
    }

    /**
     * Enable mouse input handling.
     */
    public function withMouse(): static
    {
        $this->mouseEnabled = true;
        return $this;
    }

    /**
     * Map custom key sequences to specific key names.
     */
    public function mapKey(string $sequence, string $keyName): static
    {
        $this->keyMappings[$sequence] = $keyName;
        return $this;
    }

    /**
     * Add multiple key mappings at once.
     */
    public function mapKeys(array $mappings): static
    {
        $this->keyMappings = array_merge($this->keyMappings, $mappings);
        return $this;
    }

    /**
     * Ignore specific key sequences (don't emit events for them).
     */
    public function ignore(string ...$sequences): static
    {
        $this->ignoredKeys = array_merge($this->ignoredKeys, $sequences);
        return $this;
    }

    /**
     * Disable handling of unknown escape sequences as raw input.
     */
    public function withoutRawSequences(): static
    {
        $this->enableRawSequences = false;
        return $this;
    }

    /**
     * Set default timeout for input reading.
     */
    public function timeout(float $seconds): static
    {
        $this->defaultTimeout = $seconds;
        return $this;
    }

    /**
     * Build the configured InputHandler.
     */
    public function build(): InputHandlerInterface
    {
        $handler = new InputHandler($this->terminal, $this->eventBus);
        
        $handler->setMouseEnabled($this->mouseEnabled);
        
        // We'd need to add these features to InputHandler
        // $handler->setKeyMappings($this->keyMappings);
        // $handler->setIgnoredKeys($this->ignoredKeys);
        // $handler->setRawSequencesEnabled($this->enableRawSequences);
        // $handler->setDefaultTimeout($this->defaultTimeout);
        
        return $handler;
    }
}