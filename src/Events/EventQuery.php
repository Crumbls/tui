<?php

declare(strict_types=1);

namespace Crumbls\Tui\Events;

use Crumbls\Tui\Contracts\EventInterface;

/**
 * Query builder for event history (Eloquent-style).
 */
class EventQuery
{
    private array $events;
    private array $whereConditions = [];
    private ?string $orderBy = null;
    private string $orderDirection = 'asc';
    private ?int $limit = null;
    private int $offset = 0;

    public function __construct(array $events)
    {
        $this->events = $events;
    }

    /**
     * Filter events by type.
     */
    public function whereType(string $type): static
    {
        $this->whereConditions[] = fn(EventInterface $event) => $event->getType() === $type;
        return $this;
    }

    /**
     * Filter events by timestamp range.
     */
    public function whereBetween(string $field, float $start, float $end): static
    {
        $this->whereConditions[] = function (EventInterface $event) use ($field, $start, $end) {
            $value = match ($field) {
                'timestamp' => $event->getTimestamp(),
                default => null,
            };
            return $value !== null && $value >= $start && $value <= $end;
        };
        return $this;
    }

    /**
     * Filter events after a specific timestamp.
     */
    public function after(float $timestamp): static
    {
        $this->whereConditions[] = fn(EventInterface $event) => $event->getTimestamp() > $timestamp;
        return $this;
    }

    /**
     * Filter events before a specific timestamp.
     */
    public function before(float $timestamp): static
    {
        $this->whereConditions[] = fn(EventInterface $event) => $event->getTimestamp() < $timestamp;
        return $this;
    }

    /**
     * Add custom where condition.
     */
    public function where(callable $condition): static
    {
        $this->whereConditions[] = $condition;
        return $this;
    }

    /**
     * Order results by field.
     */
    public function orderBy(string $field, string $direction = 'asc'): static
    {
        $this->orderBy = $field;
        $this->orderDirection = strtolower($direction);
        return $this;
    }

    /**
     * Order by timestamp (most common case).
     */
    public function latest(): static
    {
        return $this->orderBy('timestamp', 'desc');
    }

    /**
     * Order by timestamp ascending.
     */
    public function oldest(): static
    {
        return $this->orderBy('timestamp', 'asc');
    }

    /**
     * Limit number of results.
     */
    public function limit(int $count): static
    {
        $this->limit = $count;
        return $this;
    }

    /**
     * Take only the first N results.
     */
    public function take(int $count): static
    {
        return $this->limit($count);
    }

    /**
     * Skip N results.
     */
    public function skip(int $count): static
    {
        $this->offset = $count;
        return $this;
    }

    /**
     * Get the first event matching the query.
     */
    public function first(): ?EventInterface
    {
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * Get the last event matching the query.
     */
    public function last(): ?EventInterface
    {
        $results = $this->get();
        return end($results) ?: null;
    }

    /**
     * Count matching events.
     */
    public function count(): int
    {
        return count($this->get());
    }

    /**
     * Execute the query and get results.
     */
    public function get(): array
    {
        $results = $this->events;

        // Apply where conditions
        foreach ($this->whereConditions as $condition) {
            $results = array_filter($results, $condition);
        }

        // Apply ordering
        if ($this->orderBy) {
            usort($results, function (EventInterface $a, EventInterface $b) {
                $valueA = match ($this->orderBy) {
                    'timestamp' => $a->getTimestamp(),
                    'type' => $a->getType(),
                    default => 0,
                };
                $valueB = match ($this->orderBy) {
                    'timestamp' => $b->getTimestamp(),
                    'type' => $b->getType(),
                    default => 0,
                };

                $result = $valueA <=> $valueB;
                return $this->orderDirection === 'desc' ? -$result : $result;
            });
        }

        // Apply offset and limit
        if ($this->offset > 0) {
            $results = array_slice($results, $this->offset);
        }

        if ($this->limit !== null) {
            $results = array_slice($results, 0, $this->limit);
        }

        return array_values($results);
    }

    /**
     * Get results as array (alias for get).
     */
    public function toArray(): array
    {
        return array_map(fn(EventInterface $event) => $event->toArray(), $this->get());
    }
}