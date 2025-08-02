<?php

declare(strict_types=1);

use Crumbls\Tui\Input\InputHandler;
use Crumbls\Tui\Events\KeyInputEvent;
use Crumbls\Tui\Events\MouseInputEvent;
use Crumbls\Tui\Testing\FakeTerminal;
use Crumbls\Tui\Events\EventBus;

describe('InputHandler', function () {
    test('processes simple key input', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        $emittedEvents = [];
        $eventBus->listen('KeyInputEvent', function ($event) use (&$emittedEvents) {
            $emittedEvents[] = $event;
        });
        
        $terminal->queueInput('a');
        $result = $handler->processInput();
        
        expect($result)->toBeTrue();
        expect($emittedEvents)->toHaveCount(1);
        expect($emittedEvents[0])->toBeInstanceOf(KeyInputEvent::class);
        expect($emittedEvents[0]->getKey())->toBe('a');
        expect($emittedEvents[0]->isSpecialKey())->toBeFalse();
    });

    test('returns false when no input available', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        $result = $handler->processInput();
        
        expect($result)->toBeFalse();
    });

    test('parses arrow keys correctly', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        $emittedEvents = [];
        $eventBus->listen('KeyInputEvent', function ($event) use (&$emittedEvents) {
            $emittedEvents[] = $event;
        });
        
        $terminal->queueInput("\033[A", "\033[B", "\033[C", "\033[D");
        
        $handler->processInput();
        $handler->processInput();
        $handler->processInput();
        $handler->processInput();
        
        expect($emittedEvents)->toHaveCount(4);
        expect($emittedEvents[0]->getKey())->toBe('ArrowUp');
        expect($emittedEvents[1]->getKey())->toBe('ArrowDown');
        expect($emittedEvents[2]->getKey())->toBe('ArrowRight');
        expect($emittedEvents[3]->getKey())->toBe('ArrowLeft');
        
        foreach ($emittedEvents as $event) {
            expect($event->isSpecialKey())->toBeTrue();
        }
    });

    test('parses control characters correctly', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        $emittedEvents = [];
        $eventBus->listen('KeyInputEvent', function ($event) use (&$emittedEvents) {
            $emittedEvents[] = $event;
        });
        
        $terminal->queueInput("\t", "\n", "\033", chr(3)); // Tab, Enter, Escape, Ctrl+C
        
        $handler->processInput();
        $handler->processInput();
        $handler->processInput();
        $handler->processInput();
        
        expect($emittedEvents)->toHaveCount(4);
        expect($emittedEvents[0]->getKey())->toBe('Tab');
        expect($emittedEvents[1]->getKey())->toBe('Enter');
        expect($emittedEvents[2]->getKey())->toBe('Escape');
        expect($emittedEvents[3]->getKey())->toBe('Ctrl+C');
        expect($emittedEvents[3]->hasModifier('ctrl'))->toBeTrue();
    });

    test('handles special keys with modifiers', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        $emittedEvents = [];
        $eventBus->listen('KeyInputEvent', function ($event) use (&$emittedEvents) {
            $emittedEvents[] = $event;
        });
        
        $terminal->queueInput("\033[Z"); // Shift+Tab
        $handler->processInput();
        
        expect($emittedEvents)->toHaveCount(1);
        expect($emittedEvents[0]->getKey())->toBe('ShiftTab');
        expect($emittedEvents[0]->hasModifier('shift'))->toBeTrue();
    });

    test('parses function keys and other special sequences', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        $emittedEvents = [];
        $eventBus->listen('KeyInputEvent', function ($event) use (&$emittedEvents) {
            $emittedEvents[] = $event;
        });
        
        $terminal->queueInput("\033[H", "\033[F", "\033[3~", "\033[5~", "\033[6~");
        
        for ($i = 0; $i < 5; $i++) {
            $handler->processInput();
        }
        
        expect($emittedEvents)->toHaveCount(5);
        expect($emittedEvents[0]->getKey())->toBe('Home');
        expect($emittedEvents[1]->getKey())->toBe('End');
        expect($emittedEvents[2]->getKey())->toBe('Delete');
        expect($emittedEvents[3]->getKey())->toBe('PageUp');
        expect($emittedEvents[4]->getKey())->toBe('PageDown');
    });

    test('handles unknown escape sequences as raw input', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        $emittedEvents = [];
        $eventBus->listen('KeyInputEvent', function ($event) use (&$emittedEvents) {
            $emittedEvents[] = $event;
        });
        
        $terminal->queueInput("\033[99~"); // Unknown sequence
        $handler->processInput();
        
        expect($emittedEvents)->toHaveCount(1);
        expect($emittedEvents[0]->getKey())->toBe("\033[99~");
        expect($emittedEvents[0]->isSpecialKey())->toBeTrue();
    });

    test('handles backspace correctly', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        $emittedEvents = [];
        $eventBus->listen('KeyInputEvent', function ($event) use (&$emittedEvents) {
            $emittedEvents[] = $event;
        });
        
        $terminal->queueInput(chr(127)); // DEL character (backspace)
        $handler->processInput();
        
        expect($emittedEvents)->toHaveCount(1);
        expect($emittedEvents[0]->getKey())->toBe('Backspace');
        expect($emittedEvents[0]->isSpecialKey())->toBeTrue();
    });

    test('can enable and disable mouse input', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        expect($handler->isMouseEnabled())->toBeFalse();
        
        $handler->setMouseEnabled(true);
        expect($handler->isMouseEnabled())->toBeTrue();
        
        $handler->setMouseEnabled(false);
        expect($handler->isMouseEnabled())->toBeFalse();
    });

    test('processes input with timeout', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        // No input - should return false even with timeout
        $result = $handler->processInput(0.1);
        expect($result)->toBeFalse();
        
        // With input - should return true
        $terminal->queueInput('x');
        $result = $handler->processInput(0.1);
        expect($result)->toBeTrue();
    });

    test('handles multi-character sequences as raw input', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        $emittedEvents = [];
        $eventBus->listen('KeyInputEvent', function ($event) use (&$emittedEvents) {
            $emittedEvents[] = $event;
        });
        
        $terminal->queueInput('abc'); // Multi-character (shouldn't happen in real terminal)
        $handler->processInput();
        
        expect($emittedEvents)->toHaveCount(1);
        expect($emittedEvents[0]->getKey())->toBe('abc');
        expect($emittedEvents[0]->isSpecialKey())->toBeTrue();
    });
});