<?php

declare(strict_types=1);

namespace Crumbls\Tui\Events;

/**
 * Event fired when focus changes between widgets.
 */
class FocusChangedEvent extends Event
{
    public function __construct(
        private ?string $fromWidget,
        private ?string $toWidget
    ) {
        parent::__construct();
    }

    public function getFromWidget(): ?string
    {
        return $this->fromWidget;
    }

    public function getToWidget(): ?string
    {
        return $this->toWidget;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'from_widget' => $this->fromWidget,
            'to_widget' => $this->toWidget,
        ]);
    }
}