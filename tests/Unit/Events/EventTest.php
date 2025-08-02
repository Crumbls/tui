<?php

declare(strict_types=1);

use Crumbls\Tui\Events\Event;

// Create a concrete test event for testing
class TestEvent extends Event
{
    public function __construct(
        private string $message = 'test message'
    ) {
        parent::__construct();
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'message' => $this->message,
        ]);
    }
}

describe('Event', function () {
    test('creates event with timestamp', function () {
        $before = microtime(true);
        $event = new TestEvent();
        $after = microtime(true);
        
        expect($event->getTimestamp())->toBeGreaterThanOrEqual($before);
        expect($event->getTimestamp())->toBeLessThanOrEqual($after);
    });

    test('generates type from class name', function () {
        $event = new TestEvent();
        
        expect($event->getType())->toBe('TestEvent');
    });

    test('converts to array with base properties', function () {
        $event = new TestEvent('hello world');
        $array = $event->toArray();
        
        expect($array)->toHaveKey('type');
        expect($array)->toHaveKey('timestamp');
        expect($array)->toHaveKey('message');
        expect($array['type'])->toBe('TestEvent');
        expect($array['message'])->toBe('hello world');
        expect($array['timestamp'])->toBeFloat();
    });

    test('timestamp is immutable after creation', function () {
        $event = new TestEvent();
        $timestamp1 = $event->getTimestamp();
        
        usleep(1000); // Wait 1ms
        
        $timestamp2 = $event->getTimestamp();
        expect($timestamp1)->toBe($timestamp2);
    });
});