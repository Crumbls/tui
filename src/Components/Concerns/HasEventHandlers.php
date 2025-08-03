<?php

declare(strict_types=1);

namespace Crumbls\Tui\Components\Concerns;

use Crumbls\Tui\Event\ClickEvent;
use Crumbls\Tui\Event\KeyPressEvent;

/**
 * Trait for components that can handle events.
 */
trait HasEventHandlers
{
    /**
     * @var array<string, callable[]>
     */
    protected array $eventHandlers = [];

    public function onKeyPress(callable $handler): self
    {
        $this->eventHandlers['keyPress'][] = $handler;
        return $this;
    }

    public function onClick(callable $handler): self
    {
        $this->eventHandlers['click'][] = $handler;
        return $this;
    }

    public function onFocus(callable $handler): self
    {
        $this->eventHandlers['focus'][] = $handler;
        return $this;
    }

    public function onBlur(callable $handler): self
    {
        $this->eventHandlers['blur'][] = $handler;
        return $this;
    }

    public function handleKeyPress(KeyPressEvent $event): bool
    {
        return $this->dispatchEvent('keyPress', $event);
    }

    public function handleClick(ClickEvent $event): bool
    {
        return $this->dispatchEvent('click', $event);
    }

    public function handleFocus(): void
    {
        $this->dispatchEvent('focus');
    }

    public function handleBlur(): void
    {
        $this->dispatchEvent('blur');
    }

    /**
     * Dispatch event to registered handlers.
     */
    protected function dispatchEvent(string $eventType, mixed $event = null): bool
    {
        $handlers = $this->eventHandlers[$eventType] ?? [];
        
        foreach ($handlers as $handler) {
            $result = $handler($event);
            
            // If handler explicitly returns true, event is handled
            if ($result === true) {
                return true;
            }
        }
        
        // Event not handled, allow bubbling
        return false;
    }
}