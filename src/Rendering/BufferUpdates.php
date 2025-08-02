<?php

declare(strict_types=1);

namespace Crumbls\Tui\Rendering;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Collection of buffer updates.
 * 
 * @implements IteratorAggregate<BufferUpdate>
 */
class BufferUpdates implements Countable, IteratorAggregate
{
    /**
     * @param BufferUpdate[] $updates
     */
    public function __construct(private array $updates = [])
    {
    }

    public function add(BufferUpdate $update): void
    {
        $this->updates[] = $update;
    }

    public function count(): int
    {
        return count($this->updates);
    }

    public function isEmpty(): bool
    {
        return empty($this->updates);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->updates);
    }

    public function last(): ?BufferUpdate
    {
        if (empty($this->updates)) {
            return null;
        }

        return $this->updates[array_key_last($this->updates)];
    }

    /**
     * Get all updates as array.
     */
    public function toArray(): array
    {
        return $this->updates;
    }
}