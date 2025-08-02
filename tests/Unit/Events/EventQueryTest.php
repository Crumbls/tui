<?php

declare(strict_types=1);

use Crumbls\Tui\Events\EventQuery;
use Crumbls\Tui\Events\KeyPressedEvent;
use Crumbls\Tui\Events\FocusChangedEvent;

describe('EventQuery', function () {
    beforeEach(function () {
        // Create test events with different timestamps
        $this->events = [
            new KeyPressedEvent('a', 'context1'),
            new KeyPressedEvent('b', 'context2'),
            new FocusChangedEvent('widget1', 'widget2'),
            new KeyPressedEvent('c', 'context3'),
        ];
        
        // Manually set timestamps for predictable testing
        usleep(1000);
    });

    test('filters events by type', function () {
        $query = new EventQuery($this->events);
        $keyEvents = $query->whereType('KeyPressedEvent')->get();
        
        expect($keyEvents)->toHaveCount(3);
        foreach ($keyEvents as $event) {
            expect($event)->toBeInstanceOf(KeyPressedEvent::class);
        }
    });

    test('orders events by timestamp', function () {
        $query = new EventQuery($this->events);
        $latestEvents = $query->latest()->get();
        
        expect($latestEvents)->toHaveCount(4);
        
        // Should be in descending order by timestamp
        for ($i = 0; $i < count($latestEvents) - 1; $i++) {
            expect($latestEvents[$i]->getTimestamp())->toBeGreaterThanOrEqual(
                $latestEvents[$i + 1]->getTimestamp()
            );
        }
    });

    test('limits results', function () {
        $query = new EventQuery($this->events);
        $limitedEvents = $query->take(2)->get();
        
        expect($limitedEvents)->toHaveCount(2);
    });

    test('gets first and last events', function () {
        $query = new EventQuery($this->events);
        
        $first = $query->first();
        $last = $query->last();
        
        expect($first)->toBeInstanceOf(KeyPressedEvent::class);
        expect($last)->toBeInstanceOf(KeyPressedEvent::class);
        expect($first)->not->toBe($last);
    });

    test('counts matching events', function () {
        $totalCount = (new EventQuery($this->events))->count();
        $keyEventCount = (new EventQuery($this->events))->whereType('KeyPressedEvent')->count();
        $focusEventCount = (new EventQuery($this->events))->whereType('FocusChangedEvent')->count();
        
        expect($totalCount)->toBe(4);
        expect($keyEventCount)->toBe(3);
        expect($focusEventCount)->toBe(1);
    });

    test('chains multiple conditions', function () {
        $query = new EventQuery($this->events);
        $results = $query
            ->whereType('KeyPressedEvent')
            ->latest()
            ->take(2)
            ->get();
        
        expect($results)->toHaveCount(2);
        foreach ($results as $event) {
            expect($event)->toBeInstanceOf(KeyPressedEvent::class);
        }
    });

    test('filters with custom where condition', function () {
        $query = new EventQuery($this->events);
        $results = $query->where(function ($event) {
            return $event instanceof KeyPressedEvent && $event->getKey() === 'a';
        })->get();
        
        expect($results)->toHaveCount(1);
        expect($results[0]->getKey())->toBe('a');
    });

    test('converts to array', function () {
        $query = new EventQuery($this->events);
        $array = $query->whereType('KeyPressedEvent')->take(1)->toArray();
        
        expect($array)->toHaveCount(1);
        expect($array[0])->toBeArray();
        expect($array[0])->toHaveKey('type');
        expect($array[0])->toHaveKey('timestamp');
        expect($array[0])->toHaveKey('key');
    });

    test('handles empty results gracefully', function () {
        $query = new EventQuery([]);
        
        expect($query->get())->toBeEmpty();
        expect($query->count())->toBe(0);
        expect($query->first())->toBeNull();
        expect($query->last())->toBeNull();
    });

    test('skips results with offset', function () {
        $query = new EventQuery($this->events);
        $results = $query->skip(2)->get();
        
        expect($results)->toHaveCount(2);
    });

    test('combines skip and take for pagination', function () {
        $query = new EventQuery($this->events);
        $page2 = $query->skip(2)->take(1)->get();
        
        expect($page2)->toHaveCount(1);
    });
});