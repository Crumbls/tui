<?php

declare(strict_types=1);

use Crumbls\Tui\Events\KeyPressedEvent;

describe('KeyPressedEvent', function () {
    test('creates event with key and optional context', function () {
        $event = new KeyPressedEvent('a', 'test context');
        
        expect($event->getKey())->toBe('a');
        expect($event->getContext())->toBe('test context');
        expect($event->getType())->toBe('KeyPressedEvent');
    });

    test('creates event with key only', function () {
        $event = new KeyPressedEvent('Enter');
        
        expect($event->getKey())->toBe('Enter');
        expect($event->getContext())->toBeNull();
    });

    test('converts to array with key data', function () {
        $event = new KeyPressedEvent("\t", 'form input');
        $array = $event->toArray();
        
        expect($array)->toHaveKey('type');
        expect($array)->toHaveKey('timestamp');
        expect($array)->toHaveKey('key');
        expect($array)->toHaveKey('context');
        
        expect($array['type'])->toBe('KeyPressedEvent');
        expect($array['key'])->toBe("\t");
        expect($array['context'])->toBe('form input');
        expect($array['timestamp'])->toBeFloat();
    });

    test('handles special keys', function () {
        $escapeEvent = new KeyPressedEvent("\033[A");
        expect($escapeEvent->getKey())->toBe("\033[A");
        
        $ctrlEvent = new KeyPressedEvent(chr(3)); // Ctrl+C
        expect($ctrlEvent->getKey())->toBe(chr(3));
    });
});