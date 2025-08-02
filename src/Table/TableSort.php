<?php

declare(strict_types=1);

namespace Crumbls\Tui\Table;

class TableSort
{
    public const ASC = 'asc';
    public const DESC = 'desc';

    protected ?string $column = null;
    protected ?string $direction = null;

    public function __construct(?string $column = null, ?string $direction = null)
    {
        $this->column = $column;
        $this->direction = $direction;
    }

    public static function make(?string $column = null, ?string $direction = null): static
    {
        return new static($column, $direction);
    }

    public function by(string $column, string $direction = self::ASC): static
    {
        $this->column = $column;
        $this->direction = $direction;
        return $this;
    }

    public function toggle(string $column): static
    {
        if ($this->column === $column) {
            // Cycle through: asc -> desc -> null (unsorted)
            $this->direction = match($this->direction) {
                self::ASC => self::DESC,
                self::DESC => null,
                default => self::ASC
            };
            
            if ($this->direction === null) {
                $this->column = null;
            }
        } else {
            // New column, start with ascending
            $this->column = $column;
            $this->direction = self::ASC;
        }
        
        return $this;
    }

    public function getColumn(): ?string
    {
        return $this->column;
    }

    public function getDirection(): ?string
    {
        return $this->direction;
    }

    public function isActive(): bool
    {
        return $this->column !== null && $this->direction !== null;
    }

    public function isColumn(string $column): bool
    {
        return $this->column === $column;
    }

    public function isAscending(): bool
    {
        return $this->direction === self::ASC;
    }

    public function isDescending(): bool
    {
        return $this->direction === self::DESC;
    }

    public function getSortIndicator(string $column): string
    {
        if (!$this->isColumn($column)) {
            return '';
        }

        return match($this->direction) {
            self::ASC => ' ↑',
            self::DESC => ' ↓',
            default => ''
        };
    }

    /**
     * Apply sorting to array data.
     */
    public function applyToArray(array $data, array $columns): array
    {
        if (!$this->isActive()) {
            return $data;
        }

        $column = $this->findColumn($columns);
        if (!$column) {
            return $data;
        }

        usort($data, function($a, $b) use ($column) {
            $valueA = $column->getValue($a);
            $valueB = $column->getValue($b);

            // Handle null values
            if ($valueA === null && $valueB === null) return 0;
            if ($valueA === null) return 1;
            if ($valueB === null) return -1;

            // Numeric comparison
            if (is_numeric($valueA) && is_numeric($valueB)) {
                $result = $valueA <=> $valueB;
            } else {
                // String comparison
                $result = strcasecmp((string) $valueA, (string) $valueB);
            }

            return $this->isDescending() ? -$result : $result;
        });

        return $data;
    }

    /**
     * Get sort parameters for database queries.
     */
    public function getDatabaseSort(): array
    {
        if (!$this->isActive()) {
            return [];
        }

        return [
            'column' => $this->column,
            'direction' => $this->direction,
        ];
    }

    protected function findColumn(array $columns): ?Column
    {
        foreach ($columns as $column) {
            if ($column instanceof Column && $column->getSortColumn() === $this->column) {
                return $column;
            }
        }
        return null;
    }
}