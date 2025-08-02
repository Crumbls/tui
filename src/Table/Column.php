<?php

declare(strict_types=1);

namespace Crumbls\Tui\Table;

use Closure;

abstract class Column
{
    protected string $name;
    protected ?string $label = null;
    protected bool $sortable = false;
    protected bool $searchable = false;
    protected ?string $sortColumn = null;
    protected string $alignment = 'left';
    protected ?int $width = null;
    protected ?Closure $formatCallback = null;
    protected array $attributes = [];

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->label = $this->generateLabel($name);
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function sortable(bool|string $sortable = true): static
    {
        $this->sortable = (bool) $sortable;
        
        // If string provided, use as custom sort column
        if (is_string($sortable)) {
            $this->sortColumn = $sortable;
        }
        
        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        return $this;
    }

    public function width(int $width): static
    {
        $this->width = $width;
        return $this;
    }

    public function alignLeft(): static
    {
        $this->alignment = 'left';
        return $this;
    }

    public function alignCenter(): static
    {
        $this->alignment = 'center';
        return $this;
    }

    public function alignRight(): static
    {
        $this->alignment = 'right';
        return $this;
    }

    public function formatUsing(Closure $callback): static
    {
        $this->formatCallback = $callback;
        return $this;
    }

    // Getters
    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label ?? $this->generateLabel($this->name);
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function getSortColumn(): string
    {
        return $this->sortColumn ?? $this->name;
    }

    public function getAlignment(): string
    {
        return $this->alignment;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * Get the value from a record for this column.
     */
    public function getValue(array $record): mixed
    {
        // Support dot notation for nested data (like 'department.name')
        $keys = explode('.', $this->name);
        $value = $record;
        
        foreach ($keys as $key) {
            if (is_array($value) && isset($value[$key])) {
                $value = $value[$key];
            } else {
                return null;
            }
        }
        
        return $value;
    }

    /**
     * Format the value for display.
     */
    public function formatValue(mixed $value, array $record): string
    {
        if ($this->formatCallback) {
            $value = ($this->formatCallback)($value, $record);
        }

        return $this->formatForDisplay($value, $record);
    }

    /**
     * Column-specific formatting logic.
     */
    abstract protected function formatForDisplay(mixed $value, array $record): string;

    /**
     * Generate a label from the column name.
     */
    protected function generateLabel(string $name): string
    {
        // Convert snake_case or dot.notation to Title Case
        $label = str_replace(['.', '_'], ' ', $name);
        return ucwords($label);
    }
}