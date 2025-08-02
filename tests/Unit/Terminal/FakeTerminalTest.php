<?php

declare(strict_types=1);

use Crumbls\Tui\Testing\FakeTerminal;
use Crumbls\Tui\Terminal\Size;

describe('FakeTerminal', function () {
    test('initializes with default size', function () {
        $terminal = new FakeTerminal();
        
        expect($terminal->getSize())->toEqual(new Size(80, 24));
    });

    test('initializes with custom size', function () {
        $terminal = new FakeTerminal(100, 30);
        
        expect($terminal->getSize())->toEqual(new Size(100, 30));
    });

    test('queues and reads input correctly', function () {
        $terminal = new FakeTerminal();
        
        // No input returns null
        expect($terminal->readKey())->toBeNull();
        
        // Queue some input
        $terminal->queueInput('a', 'b', "\033[A");
        
        expect($terminal->readKey())->toBe('a');
        expect($terminal->readKey())->toBe('b');
        expect($terminal->readKey())->toBe("\033[A");
        expect($terminal->readKey())->toBeNull();
    });

    test('captures output correctly', function () {
        $terminal = new FakeTerminal();
        
        $terminal->write('Hello');
        $terminal->write(' World');
        
        expect($terminal->getOutput())->toBe(['Hello', ' World']);
        expect($terminal->getOutputAsString())->toBe('Hello World');
    });

    test('handles clear command', function () {
        $terminal = new FakeTerminal();
        
        $terminal->clear();
        
        expect($terminal->getOutputAsString())->toBe("\033[H\033[2J");
    });

    test('tracks raw mode state', function () {
        $terminal = new FakeTerminal();
        
        expect($terminal->isRawModeEnabled())->toBeFalse();
        
        $terminal->enableRawMode();
        expect($terminal->isRawModeEnabled())->toBeTrue();
        
        $terminal->disableRawMode();
        expect($terminal->isRawModeEnabled())->toBeFalse();
    });

    test('allows configuring capabilities', function () {
        $terminal = new FakeTerminal();
        
        // Default supports both
        expect($terminal->supportsColors())->toBeTrue();
        expect($terminal->supportsMouse())->toBeTrue();
        
        $terminal->setSupportsColors(false);
        $terminal->setSupportsMouse(false);
        
        expect($terminal->supportsColors())->toBeFalse();
        expect($terminal->supportsMouse())->toBeFalse();
    });

    test('allows resizing for testing', function () {
        $terminal = new FakeTerminal();
        
        $newSize = new Size(120, 40);
        $terminal->setSize($newSize);
        
        expect($terminal->getSize())->toEqual($newSize);
    });

    test('clears output buffer', function () {
        $terminal = new FakeTerminal();
        
        $terminal->write('test');
        expect($terminal->getOutput())->not->toBeEmpty();
        
        $terminal->clearOutput();
        expect($terminal->getOutput())->toBeEmpty();
    });

    test('handles multiple input queuing', function () {
        $terminal = new FakeTerminal();
        
        $terminal->queueInput('a', 'b');
        $terminal->queueInput('c', 'd');
        
        expect($terminal->readKey())->toBe('a');
        expect($terminal->readKey())->toBe('b');
        expect($terminal->readKey())->toBe('c');
        expect($terminal->readKey())->toBe('d');
        expect($terminal->readKey())->toBeNull();
    });

    test('tracks mouse reporting enable/disable', function () {
        $terminal = new FakeTerminal();
        
        $terminal->enableMouseReporting();
        expect($terminal->getOutput())->toContain('MOUSE_REPORTING_ENABLED');
        
        $terminal->disableMouseReporting();
        expect($terminal->getOutput())->toContain('MOUSE_REPORTING_DISABLED');
    });

    test('can queue mouse click events', function () {
        $terminal = new FakeTerminal();
        
        $terminal->queueMouseClick(10, 5, 'left', 'press');
        $input = $terminal->readKey();
        
        expect($input)->toBe("\033[<0;10;5M");
    });

    test('can queue mouse scroll events', function () {
        $terminal = new FakeTerminal();
        
        $terminal->queueMouseScroll(15, 8, 'up');
        $input = $terminal->readKey();
        
        expect($input)->toBe("\033[<64;15;8M");
        
        $terminal->queueMouseScroll(15, 8, 'down');
        $input = $terminal->readKey();
        
        expect($input)->toBe("\033[<65;15;8M");
    });

    test('can queue different mouse buttons', function () {
        $terminal = new FakeTerminal();
        
        $terminal->queueMouseClick(5, 3, 'left', 'press');
        $terminal->queueMouseClick(5, 3, 'right', 'press');
        $terminal->queueMouseClick(5, 3, 'middle', 'press');
        
        expect($terminal->readKey())->toBe("\033[<0;5;3M");  // left
        expect($terminal->readKey())->toBe("\033[<2;5;3M");  // right  
        expect($terminal->readKey())->toBe("\033[<1;5;3M");  // middle
    });

    test('can queue mouse press and release', function () {
        $terminal = new FakeTerminal();
        
        $terminal->queueMouseClick(10, 5, 'left', 'press');
        $terminal->queueMouseClick(10, 5, 'left', 'release');
        
        expect($terminal->readKey())->toBe("\033[<0;10;5M");  // press
        expect($terminal->readKey())->toBe("\033[<0;10;5m");  // release
    });
});