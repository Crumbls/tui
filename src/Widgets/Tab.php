<?php

declare(strict_types=1);

namespace Crumbls\Tui\Widgets;

class Tab
{
    protected string $label;
    protected $content;
    protected ?string $shortcut = null;
    protected bool $navigable = true;
    protected $onSelected = null;

    public static function make(string $label, $content): static
    {
        $tab = new static();
        $tab->label = $label;
        $tab->content = $content;
        return $tab;
    }

    public function shortcut(string $key): static
    {
        $this->shortcut = $key;
        return $this;
    }

    public function getShortcut(): ?string
    {
        return $this->shortcut;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function navigable(bool $value = true): static
    {
        $this->navigable = $value;
        return $this;
    }

    public function isNavigable(): bool
    {
        return $this->navigable;
    }

    public function onSelected(callable $callback): static
    {
        $this->onSelected = $callback;
        return $this;
    }

    public function getContent()
    {
        if ($this->onSelected) {
            return call_user_func($this->onSelected, $this);
        }
        return $this->content;
    }
}
