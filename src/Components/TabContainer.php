<?php

declare(strict_types=1);

namespace Crumbls\Tui\Components;

use Crumbls\Tui\Contracts\WidgetInterface;
use Crumbls\Tui\Layout\Layout;
use Crumbls\Tui\Widgets\Tabs;
use Crumbls\Tui\Widgets\Divider;
use Crumbls\Tui\Widgets\Paragraph;

class TabContainer implements WidgetInterface
{
    protected array $tabs = [];
    protected array $content = [];
    protected int $selected = 0;

    public static function make(): static
    {
        return new static();
    }

    public function addTab(string $title, WidgetInterface $content): static
    {
        $this->tabs[] = $title;
        $this->content[] = $content;
        return $this;
    }

    public function selected(int $index): static
    {
        $this->selected = max(0, min($index, count($this->tabs) - 1));
        return $this;
    }

    public function nextTab(): static
    {
        $this->selected = ($this->selected + 1) % count($this->tabs);
        return $this;
    }

    public function previousTab(): static
    {
        $this->selected = ($this->selected - 1 + count($this->tabs)) % count($this->tabs);
        return $this;
    }

    public function getSelected(): int
    {
        return $this->selected;
    }

    protected ?int $width = null;
    protected ?int $height = null;

    public function setRegion(int $width, int $height): static
    {
        $this->width = $width;
        $this->height = $height;
        return $this;
    }

    public function render(): string
    {
        if (empty($this->tabs)) {
            return '';
        }

        $layout = Layout::vertical([
            Tabs::from($this->tabs, $this->selected),
            Divider::make(),
            $this->content[$this->selected] ?? Paragraph::make('No content'),
        ]);

        // Pass our size to the layout
        if ($this->width && $this->height) {
            $layout->setRegion($this->width, $this->height);
        }

        return $layout->render();
    }

    public function toArray(): array
    {
        return [
            'type' => 'tab_container',
            'tabs' => $this->tabs,
            'selected' => $this->selected,
        ];
    }
}