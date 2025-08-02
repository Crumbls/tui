<?php

declare(strict_types=1);

use Crumbls\Tui\Events\MouseInputEvent;

describe('MouseInputEvent', function () {
    test('creates mouse click event', function () {
        $event = new MouseInputEvent('click', 10, 5, 'left', "\033[M !!");
        
        expect($event->getAction())->toBe('click');
        expect($event->getX())->toBe(10);
        expect($event->getY())->toBe(5);
        expect($event->getButton())->toBe('left');
        expect($event->getRawInput())->toBe("\033[M !!");
        expect($event->getInputType())->toBe('mouse');
        expect($event->shouldHandle())->toBeTrue();
    });

    test('creates mouse event with modifiers', function () {
        $event = new MouseInputEvent('click', 0, 0, 'right', "\033[M\"!!", ['ctrl']);
        
        expect($event->getModifiers())->toBe(['ctrl']);
        expect($event->hasModifier('ctrl'))->toBeTrue();
        expect($event->hasModifier('shift'))->toBeFalse();
    });

    test('handles different mouse actions', function () {
        $pressEvent = new MouseInputEvent('press', 1, 1, 'left', 'raw1');
        $releaseEvent = new MouseInputEvent('release', 1, 1, 'left', 'raw2');
        $moveEvent = new MouseInputEvent('move', 2, 2, 'none', 'raw3');
        $scrollEvent = new MouseInputEvent('scroll', 3, 3, 'middle', 'raw4');
        
        expect($pressEvent->getAction())->toBe('press');
        expect($releaseEvent->getAction())->toBe('release');
        expect($moveEvent->getAction())->toBe('move');
        expect($scrollEvent->getAction())->toBe('scroll');
        
        expect($moveEvent->getButton())->toBe('none');
        expect($scrollEvent->getButton())->toBe('middle');
    });

    test('converts to array with all properties', function () {
        $event = new MouseInputEvent('click', 15, 8, 'right', 'raw', ['shift', 'alt']);
        $array = $event->toArray();
        
        expect($array)->toHaveKey('type');
        expect($array)->toHaveKey('timestamp');
        expect($array)->toHaveKey('input_type');
        expect($array)->toHaveKey('action');
        expect($array)->toHaveKey('x');
        expect($array)->toHaveKey('y');
        expect($array)->toHaveKey('button');
        expect($array)->toHaveKey('raw_input');
        expect($array)->toHaveKey('modifiers');
        
        expect($array['input_type'])->toBe('mouse');
        expect($array['action'])->toBe('click');
        expect($array['x'])->toBe(15);
        expect($array['y'])->toBe(8);
        expect($array['button'])->toBe('right');
        expect($array['raw_input'])->toBe('raw');
        expect($array['modifiers'])->toBe(['shift', 'alt']);
    });

    test('handles coordinates at terminal boundaries', function () {
        $event = new MouseInputEvent('click', 0, 0, 'left', 'raw');
        expect($event->getX())->toBe(0);
        expect($event->getY())->toBe(0);
        
        $event2 = new MouseInputEvent('click', 999, 999, 'left', 'raw');
        expect($event2->getX())->toBe(999);
        expect($event2->getY())->toBe(999);
    });
});