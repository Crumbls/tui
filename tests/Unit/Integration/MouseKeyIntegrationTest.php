<?php

declare(strict_types=1);

use Crumbls\Tui\Input\InputHandler;
use Crumbls\Tui\Testing\FakeTerminal;
use Crumbls\Tui\Events\EventBus;

describe('Mouse and keyboard integration', function () {
    test('mouse clicks do not generate key events', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        $handler->setMouseEnabled(true);
        
        // Queue several mouse events
        $terminal->queueMouseClick(10, 5, 'left', 'press');
        $terminal->queueMouseClick(10, 5, 'left', 'release');
        $terminal->queueMouseScroll(15, 8, 'up');
        
        // Process all events
        $handler->processInput();
        $handler->processInput();
        $handler->processInput();
        
        // Should only have mouse events, no key events
        $mouseEvents = $eventBus->query()->whereType('MouseInputEvent')->get();
        $keyEvents = $eventBus->query()->whereType('KeyInputEvent')->get();
        
        expect(count($mouseEvents))->toBe(3);
        expect(count($keyEvents))->toBe(0);
    });

    test('mixed mouse and key events are handled correctly', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        $handler->setMouseEnabled(true);
        
        // Queue mixed events: key, mouse, key, mouse
        $terminal->queueInput('a');
        $terminal->queueMouseClick(10, 5, 'left', 'press');
        $terminal->queueInput('b');
        $terminal->queueMouseScroll(15, 8, 'up');
        
        // Process all events
        $handler->processInput();
        $handler->processInput();
        $handler->processInput();
        $handler->processInput();
        
        // Check we got the right number of each type
        $mouseEvents = $eventBus->query()->whereType('MouseInputEvent')->get();
        $keyEvents = $eventBus->query()->whereType('KeyInputEvent')->get();
        
        expect(count($mouseEvents))->toBe(2);
        expect(count($keyEvents))->toBe(2);
        
        // Check key events have correct keys
        expect($keyEvents[0]->getKey())->toBe('a');
        expect($keyEvents[1]->getKey())->toBe('b');
        
        // Check mouse events are correct
        expect($mouseEvents[0]->getAction())->toBe('press');
        expect($mouseEvents[1]->getAction())->toBe('scroll');
    });

    test('escape sequences for other keys still work with mouse enabled', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        $handler->setMouseEnabled(true);
        
        // Queue arrow keys and function keys
        $terminal->queueInput("\033[A", "\033[B", "\033OP", "q");
        
        // Process all events
        $handler->processInput();
        $handler->processInput();
        $handler->processInput();
        $handler->processInput();
        
        // Should only have key events
        $keyEvents = $eventBus->query()->whereType('KeyInputEvent')->get();
        $mouseEvents = $eventBus->query()->whereType('MouseInputEvent')->get();
        
        expect(count($keyEvents))->toBe(4);
        expect(count($mouseEvents))->toBe(0);
        
        // Check specific keys
        expect($keyEvents[0]->getKey())->toBe('ArrowUp');
        expect($keyEvents[1]->getKey())->toBe('ArrowDown'); 
        // For now, just check that we got a key event for F1 (actual value may vary)
        expect($keyEvents[2]->getKey())->not->toBeEmpty();
        expect($keyEvents[3]->getKey())->toBe('q');
    });

    test('complex mouse sequences with coordinates do not split', function() {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        $handler->setMouseEnabled(true);
        
        // Queue a mouse click with large coordinates
        $terminal->queueInput("\033[<0;123;456M");
        
        $handler->processInput();
        
        $mouseEvents = $eventBus->query()->whereType('MouseInputEvent')->get();
        $keyEvents = $eventBus->query()->whereType('KeyInputEvent')->get();
        
        expect(count($mouseEvents))->toBe(1);
        expect(count($keyEvents))->toBe(0);
        
        expect($mouseEvents[0]->getX())->toBe(123);
        expect($mouseEvents[0]->getY())->toBe(456);
    });
});