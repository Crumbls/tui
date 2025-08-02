<?php

declare(strict_types=1);

use Crumbls\Tui\Events\KeyInputEvent;

describe('KeyInputEvent', function () {
    test('creates simple key event', function () {
        $event = new KeyInputEvent('a', 'a');
        
        expect($event->getKey())->toBe('a');
        expect($event->getRawInput())->toBe('a');
        expect($event->getInputType())->toBe('key');
        expect($event->isSpecialKey())->toBeFalse();
        expect($event->getModifiers())->toBeEmpty();
        expect($event->shouldHandle())->toBeTrue();
    });

    test('creates special key event', function () {
        $event = new KeyInputEvent('ArrowUp', "\033[A", true);
        
        expect($event->getKey())->toBe('ArrowUp');
        expect($event->getRawInput())->toBe("\033[A");
        expect($event->isSpecialKey())->toBeTrue();
    });

    test('creates key event with modifiers', function () {
        $event = new KeyInputEvent('Tab', "\t", true, ['shift']);
        
        expect($event->getKey())->toBe('Tab');
        expect($event->getModifiers())->toBe(['shift']);
        expect($event->hasModifier('shift'))->toBeTrue();
        expect($event->hasModifier('ctrl'))->toBeFalse();
    });

    test('converts to array with all properties', function () {
        $event = new KeyInputEvent('Ctrl+C', chr(3), true, ['ctrl']);
        $array = $event->toArray();
        
        expect($array)->toHaveKey('type');
        expect($array)->toHaveKey('timestamp');
        expect($array)->toHaveKey('input_type');
        expect($array)->toHaveKey('key');
        expect($array)->toHaveKey('raw_input');
        expect($array)->toHaveKey('is_special_key');
        expect($array)->toHaveKey('modifiers');
        
        expect($array['input_type'])->toBe('key');
        expect($array['key'])->toBe('Ctrl+C');
        expect($array['raw_input'])->toBe(chr(3));
        expect($array['is_special_key'])->toBeTrue();
        expect($array['modifiers'])->toBe(['ctrl']);
    });

    test('handles multiple modifiers', function () {
        $event = new KeyInputEvent('F1', "\033[11~", true, ['shift', 'ctrl']);
        
        expect($event->hasModifier('shift'))->toBeTrue();
        expect($event->hasModifier('ctrl'))->toBeTrue();
        expect($event->hasModifier('alt'))->toBeFalse();
    });
});