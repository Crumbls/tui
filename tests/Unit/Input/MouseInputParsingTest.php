<?php

declare(strict_types=1);

use Crumbls\Tui\Input\InputHandler;
use Crumbls\Tui\Events\MouseInputEvent;
use Crumbls\Tui\Testing\FakeTerminal;
use Crumbls\Tui\Events\EventBus;

describe('InputHandler mouse parsing', function () {
    test('parses SGR format left click press', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        // SGR format: \033[<0;10;5M (left button press at x=10, y=5)
        $event = $handler->parseMouseEvent("\033[<0;10;5M");
        
        expect($event)->toBeInstanceOf(MouseInputEvent::class);
        expect($event->getAction())->toBe('press');
        expect($event->getX())->toBe(10);
        expect($event->getY())->toBe(5);
        expect($event->getButton())->toBe('left');
        expect($event->getModifiers())->toBe([]);
        expect($event->getRawInput())->toBe("\033[<0;10;5M");
    });

    test('parses SGR format left click release', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        // SGR format: \033[<0;10;5m (left button release)
        $event = $handler->parseMouseEvent("\033[<0;10;5m");
        
        expect($event)->toBeInstanceOf(MouseInputEvent::class);
        expect($event->getAction())->toBe('release');
        expect($event->getX())->toBe(10);
        expect($event->getY())->toBe(5);
        expect($event->getButton())->toBe('left');
    });

    test('parses SGR format right click', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        // SGR format: \033[<2;15;8M (right button press)
        $event = $handler->parseMouseEvent("\033[<2;15;8M");
        
        expect($event)->toBeInstanceOf(MouseInputEvent::class);
        expect($event->getAction())->toBe('press');
        expect($event->getX())->toBe(15);
        expect($event->getY())->toBe(8);
        expect($event->getButton())->toBe('right');
    });

    test('parses SGR format middle click', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        // SGR format: \033[<1;20;12M (middle button press)
        $event = $handler->parseMouseEvent("\033[<1;20;12M");
        
        expect($event)->toBeInstanceOf(MouseInputEvent::class);
        expect($event->getAction())->toBe('press');
        expect($event->getX())->toBe(20);
        expect($event->getY())->toBe(12);
        expect($event->getButton())->toBe('middle');
    });

    test('parses SGR format with modifiers', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        // SGR format: \033[<16;10;5M (left click with ctrl)
        // Ctrl modifier adds 16 to button code
        $event = $handler->parseMouseEvent("\033[<16;10;5M");
        
        expect($event)->toBeInstanceOf(MouseInputEvent::class);
        expect($event->getButton())->toBe('left');
        expect($event->getModifiers())->toBe(['ctrl']);
    });

    test('parses SGR format with multiple modifiers', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        // SGR format: \033[<28;10;5M (left click with shift+alt+ctrl)
        // Shift=4, Alt=8, Ctrl=16 -> 4+8+16=28
        $event = $handler->parseMouseEvent("\033[<28;10;5M");
        
        expect($event)->toBeInstanceOf(MouseInputEvent::class);
        expect($event->getButton())->toBe('left');
        expect($event->getModifiers())->toContain('shift');
        expect($event->getModifiers())->toContain('alt');
        expect($event->getModifiers())->toContain('ctrl');
        expect(count($event->getModifiers()))->toBe(3);
    });

    test('parses SGR format scroll up', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        // SGR format: \033[<64;10;5M (scroll up)
        $event = $handler->parseMouseEvent("\033[<64;10;5M");
        
        expect($event)->toBeInstanceOf(MouseInputEvent::class);
        expect($event->getAction())->toBe('scroll');
        expect($event->getButton())->toBe('up');
        expect($event->getX())->toBe(10);
        expect($event->getY())->toBe(5);
    });

    test('parses SGR format scroll down', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        // SGR format: \033[<65;10;5M (scroll down)
        $event = $handler->parseMouseEvent("\033[<65;10;5M");
        
        expect($event)->toBeInstanceOf(MouseInputEvent::class);
        expect($event->getAction())->toBe('scroll');
        expect($event->getButton())->toBe('down');
    });

    test('parses old format mouse event', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        // Old format: \033[M + 3 bytes
        // Button 0 (left), x=42 (32+10), y=37 (32+5)
        $sequence = "\033[M" . chr(32) . chr(42) . chr(37);
        $event = $handler->parseMouseEvent($sequence);
        
        expect($event)->toBeInstanceOf(MouseInputEvent::class);
        expect($event->getAction())->toBe('click');
        expect($event->getX())->toBe(10);
        expect($event->getY())->toBe(5);
        expect($event->getButton())->toBe('left');
    });

    test('handles malformed mouse sequences', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        // Invalid sequences should return null
        expect($handler->parseMouseEvent("\033[<invalid"))->toBeNull();
        expect($handler->parseMouseEvent("\033[M"))->toBeNull(); // Too short
        expect($handler->parseMouseEvent("not_mouse"))->toBeNull();
        expect($handler->parseMouseEvent(""))->toBeNull();
    });

    test('identifies mouse sequences correctly', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        // Enable mouse to test detection
        $handler->setMouseEnabled(true);
        
        // Test with fake terminal mouse events
        $terminal->queueMouseClick(10, 5, 'left', 'press');
        $result = $handler->processInput();
        
        expect($result)->toBeTrue();
        
        // Check that mouse event was emitted
        $events = $eventBus->query()->whereType('MouseInputEvent')->get();
        expect(count($events))->toBe(1);
        
        $event = $events[0];
        expect($event->getX())->toBe(10);
        expect($event->getY())->toBe(5);
        expect($event->getButton())->toBe('left');
        expect($event->getAction())->toBe('press');
    });

    test('handles mouse scroll events through fake terminal', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        $handler->setMouseEnabled(true);
        
        // Queue scroll events
        $terminal->queueMouseScroll(15, 8, 'up');
        $terminal->queueMouseScroll(15, 8, 'down');
        
        // Process both events
        $handler->processInput();
        $handler->processInput();
        
        $events = $eventBus->query()->whereType('MouseInputEvent')->get();
        expect(count($events))->toBe(2);
        
        expect($events[0]->getAction())->toBe('scroll');
        expect($events[0]->getButton())->toBe('up');
        
        expect($events[1]->getAction())->toBe('scroll');
        expect($events[1]->getButton())->toBe('down');
    });

    test('handles different button clicks through fake terminal', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        $handler->setMouseEnabled(true);
        
        // Queue different button clicks
        $terminal->queueMouseClick(10, 5, 'left', 'press');
        $terminal->queueMouseClick(10, 5, 'right', 'press');
        $terminal->queueMouseClick(10, 5, 'middle', 'press');
        
        // Process all events
        $handler->processInput();
        $handler->processInput();
        $handler->processInput();
        
        $events = $eventBus->query()->whereType('MouseInputEvent')->get();
        expect(count($events))->toBe(3);
        
        expect($events[0]->getButton())->toBe('left');
        expect($events[1]->getButton())->toBe('right');
        expect($events[2]->getButton())->toBe('middle');
    });
});