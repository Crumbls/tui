<?php

declare(strict_types=1);

use Crumbls\Tui\Events\EventBus;
use Crumbls\Tui\Events\KeyPressedEvent;
use Crumbls\Tui\Events\FocusChangedEvent;

describe('EventBus', function () {
    test('creates event bus with default history size', function () {
        $bus = new EventBus();
        
        expect($bus->getEventHistory())->toBeEmpty();
        expect($bus->getStats()['events_in_history'])->toBe(0);
    });

    test('creates event bus with custom history size', function () {
        $bus = new EventBus(50);
        
        expect($bus->getEventHistory())->toBeEmpty();
    });

    test('emits events to listeners', function () {
        $bus = new EventBus();
        $called = false;
        $receivedEvent = null;
        
        $bus->listen('KeyPressedEvent', function ($event) use (&$called, &$receivedEvent) {
            $called = true;
            $receivedEvent = $event;
        });
        
        $event = new KeyPressedEvent('a');
        $bus->emit($event);
        
        expect($called)->toBeTrue();
        expect($receivedEvent)->toBe($event);
    });

    test('stores events in history', function () {
        $bus = new EventBus();
        
        $event1 = new KeyPressedEvent('a');
        $event2 = new KeyPressedEvent('b');
        
        $bus->emit($event1);
        $bus->emit($event2);
        
        $history = $bus->getEventHistory();
        expect($history)->toHaveCount(2);
        expect($history[0])->toBe($event1);
        expect($history[1])->toBe($event2);
    });

    test('trims history when it exceeds max size', function () {
        $bus = new EventBus(2); // Very small history
        
        $event1 = new KeyPressedEvent('1');
        $event2 = new KeyPressedEvent('2');
        $event3 = new KeyPressedEvent('3');
        
        $bus->emit($event1);
        $bus->emit($event2);
        $bus->emit($event3);
        
        $history = $bus->getEventHistory();
        expect($history)->toHaveCount(2);
        expect($history[0])->toBe($event2);
        expect($history[1])->toBe($event3);
    });

    test('supports multiple listeners for same event type', function () {
        $bus = new EventBus();
        $calls = [];
        
        $bus->listen('KeyPressedEvent', function ($event) use (&$calls) {
            $calls[] = 'listener1';
        });
        
        $bus->listen('KeyPressedEvent', function ($event) use (&$calls) {
            $calls[] = 'listener2';
        });
        
        $bus->emit(new KeyPressedEvent('a'));
        
        expect($calls)->toBe(['listener1', 'listener2']);
    });

    test('only calls listeners for matching event types', function () {
        $bus = new EventBus();
        $keyCalls = 0;
        $focusCalls = 0;
        
        $bus->listen('KeyPressedEvent', function () use (&$keyCalls) {
            $keyCalls++;
        });
        
        $bus->listen('FocusChangedEvent', function () use (&$focusCalls) {
            $focusCalls++;
        });
        
        $bus->emit(new KeyPressedEvent('a'));
        $bus->emit(new FocusChangedEvent('widget1', 'widget2'));
        
        expect($keyCalls)->toBe(1);
        expect($focusCalls)->toBe(1);
    });

    test('can remove listeners', function () {
        $bus = new EventBus();
        $called = false;
        
        $listener = function () use (&$called) {
            $called = true;
        };
        
        $bus->listen('KeyPressedEvent', $listener);
        $bus->unlisten('KeyPressedEvent', $listener);
        
        $bus->emit(new KeyPressedEvent('a'));
        
        expect($called)->toBeFalse();
    });

    test('can get listeners for event type', function () {
        $bus = new EventBus();
        
        $listener1 = fn() => null;
        $listener2 = fn() => null;
        
        $bus->listen('KeyPressedEvent', $listener1);
        $bus->listen('KeyPressedEvent', $listener2);
        
        $listeners = $bus->getListeners('KeyPressedEvent');
        expect($listeners)->toHaveCount(2);
        expect($listeners)->toContain($listener1);
        expect($listeners)->toContain($listener2);
    });

    test('returns empty array for event type with no listeners', function () {
        $bus = new EventBus();
        
        expect($bus->getListeners('NonexistentEvent'))->toBeEmpty();
    });

    test('can clear all listeners', function () {
        $bus = new EventBus();
        
        $bus->listen('KeyPressedEvent', fn() => null);
        $bus->listen('FocusChangedEvent', fn() => null);
        
        expect($bus->getListeners('KeyPressedEvent'))->not->toBeEmpty();
        
        $bus->clearListeners();
        
        expect($bus->getListeners('KeyPressedEvent'))->toBeEmpty();
        expect($bus->getListeners('FocusChangedEvent'))->toBeEmpty();
    });

    test('can clear event history', function () {
        $bus = new EventBus();
        
        $bus->emit(new KeyPressedEvent('a'));
        expect($bus->getEventHistory())->not->toBeEmpty();
        
        $bus->clearHistory();
        expect($bus->getEventHistory())->toBeEmpty();
    });

    test('provides stats about event bus', function () {
        $bus = new EventBus();
        
        $bus->listen('KeyPressedEvent', fn() => null);
        $bus->listen('KeyPressedEvent', fn() => null);
        $bus->listen('FocusChangedEvent', fn() => null);
        
        $bus->emit(new KeyPressedEvent('a'));
        $bus->emit(new KeyPressedEvent('b'));
        
        $stats = $bus->getStats();
        
        expect($stats['total_event_types'])->toBe(2);
        expect($stats['total_listeners'])->toBe(3);
        expect($stats['events_in_history'])->toBe(2);
        expect($stats['listener_counts']['KeyPressedEvent'])->toBe(2);
        expect($stats['listener_counts']['FocusChangedEvent'])->toBe(1);
    });

    test('handles listener exceptions gracefully', function () {
        $bus = new EventBus();
        $goodListenerCalled = false;
        
        // Add a listener that throws
        $bus->listen('KeyPressedEvent', function () {
            throw new \Exception('Bad listener');
        });
        
        // Add a good listener
        $bus->listen('KeyPressedEvent', function () use (&$goodListenerCalled) {
            $goodListenerCalled = true;
        });
        
        // This should not throw, and good listener should still be called
        $bus->emit(new KeyPressedEvent('a'));
        
        expect($goodListenerCalled)->toBeTrue();
    });
});