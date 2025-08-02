<?php

declare(strict_types=1);

namespace Crumbls\Tui\Table\Columns;

use Crumbls\Tui\Table\Column;

class TextColumn extends Column
{
    protected bool $wrap = false;
    protected ?int $limit = null;
    protected bool $isMoney = false;
    protected string $currency = 'USD';

    public function wrap(bool $wrap = true): static
    {
        $this->wrap = $wrap;
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function money(string $currency = 'USD'): static
    {
        $this->isMoney = true;
        $this->currency = $currency;
        $this->alignRight();
        return $this;
    }

    protected function formatForDisplay(mixed $value, array $record): string
    {
        if ($value === null) {
            return '';
        }

        $text = (string) $value;

        // Apply money formatting
        if ($this->isMoney && is_numeric($value)) {
            $symbol = match($this->currency) {
                'USD' => '$',
                'EUR' => '€',
                'GBP' => '£',
                default => $this->currency . ' '
            };
            $text = $symbol . number_format((float) $value, 2);
        }

        // Apply text limit
        if ($this->limit && strlen($text) > $this->limit) {
            $text = substr($text, 0, $this->limit - 3) . '...';
        }

        return $text;
    }
}