<?php

declare(strict_types=1);

namespace Crumbls\Tui\Events;

use Crumbls\Tui\Contracts\InputEventInterface;
use Crumbls\Tui\Events\Event;

/**
 * Represents a keyboard input event.
 */
class KeyInputEvent extends Event implements InputEventInterface
{
    public function __construct(
        private string $key,
        private string $rawInput,
        private bool $isSpecialKey = false,
        private array $modifiers = []
    ) {
        parent::__construct();
    }

    public function getInputType(): string
    {
        return 'key';
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getRawInput(): string
    {
        return $this->rawInput;
    }

    public function isSpecialKey(): bool
    {
        return $this->isSpecialKey;
    }

    public function getModifiers(): array
    {
        return $this->modifiers;
    }

    public function hasModifier(string $modifier): bool
    {
        return in_array($modifier, $this->modifiers);
    }

    public function shouldHandle(): bool
    {
        // Handle all key events by default
        return true;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'input_type' => $this->getInputType(),
            'key' => $this->key,
            'raw_input' => $this->rawInput,
            'is_special_key' => $this->isSpecialKey,
            'modifiers' => $this->modifiers,
        ]);
    }
}