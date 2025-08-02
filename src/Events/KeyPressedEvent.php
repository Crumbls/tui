<?php

declare(strict_types=1);

namespace Crumbls\Tui\Events;

/**
 * Event fired when a key is pressed.
 */
class KeyPressedEvent extends Event
{
    public function __construct(
        private string $key,
        private ?string $context = null
    ) {
        parent::__construct();
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getContext(): ?string
    {
        return $this->context;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'key' => $this->key,
            'context' => $this->context,
        ]);
    }
}