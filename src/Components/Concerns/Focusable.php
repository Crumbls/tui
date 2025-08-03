<?php

declare(strict_types=1);

namespace Crumbls\Tui\Components\Concerns;

use Crumbls\Tui\Components\Contracts\Component;

/**
 * Trait for components that can receive focus.
 */
trait Focusable
{
    protected bool $canFocus = false;
    protected bool $hasFocus = false;
    protected ?Component $parent = null;

    public function canFocus(): bool
    {
        return $this->canFocus;
    }

    public function hasFocus(): bool
    {
        return $this->hasFocus;
    }

    public function focus(): void
    {
        if (!$this->canFocus()) {
            return;
        }

        $this->hasFocus = true;
        $this->handleFocus();
    }

    public function blur(): void
    {
        if (!$this->hasFocus) {
            return;
        }

        $this->hasFocus = false;
        $this->handleBlur();
    }

    public function focusable(bool $focusable = true): self
    {
        $this->canFocus = $focusable;
        return $this;
    }

    public function getParent(): ?Component
    {
        return $this->parent;
    }

    public function setParent(?Component $parent): void
    {
        $this->parent = $parent;
    }
}