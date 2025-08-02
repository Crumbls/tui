<?php

declare(strict_types=1);

use Crumbls\Tui\Testing\FakeTerminal;

describe('Terminal mouse sequence reading', function () {
    test('reads complete SGR mouse sequences', function () {
        $terminal = new FakeTerminal();
        
        // Queue a complete SGR mouse sequence
        $terminal->queueInput("\033[<0;10;5M");
        
        $sequence = $terminal->readKey();
        expect($sequence)->toBe("\033[<0;10;5M");
        
        // Should not have any remaining partial sequences
        expect($terminal->readKey())->toBeNull();
    });

    test('reads mouse release sequences', function () {
        $terminal = new FakeTerminal();
        
        // Queue a mouse release sequence (lowercase 'm')
        $terminal->queueInput("\033[<0;10;5m");
        
        $sequence = $terminal->readKey();
        expect($sequence)->toBe("\033[<0;10;5m");
    });

    test('reads scroll wheel sequences', function () {
        $terminal = new FakeTerminal();
        
        // Queue scroll up and scroll down
        $terminal->queueInput("\033[<64;15;8M", "\033[<65;15;8M");
        
        expect($terminal->readKey())->toBe("\033[<64;15;8M");
        expect($terminal->readKey())->toBe("\033[<65;15;8M");
    });

    test('handles complex mouse sequences with modifiers', function () {
        $terminal = new FakeTerminal();
        
        // Queue a complex sequence (Ctrl+Shift+Alt+click = 28)
        $terminal->queueInput("\033[<28;25;17M");
        
        $sequence = $terminal->readKey();
        expect($sequence)->toBe("\033[<28;25;17M");
    });

    test('still handles regular escape sequences', function () {
        $terminal = new FakeTerminal();
        
        // Queue regular arrow keys
        $terminal->queueInput("\033[A", "\033[B", "\033[C", "\033[D");
        
        expect($terminal->readKey())->toBe("\033[A"); // Up
        expect($terminal->readKey())->toBe("\033[B"); // Down  
        expect($terminal->readKey())->toBe("\033[C"); // Right
        expect($terminal->readKey())->toBe("\033[D"); // Left
    });

    test('handles function keys', function () {
        $terminal = new FakeTerminal();
        
        // Queue some function keys
        $terminal->queueInput("\033OP", "\033OQ", "\033[15~");
        
        expect($terminal->readKey())->toBe("\033OP");   // F1
        expect($terminal->readKey())->toBe("\033OQ");   // F2
        expect($terminal->readKey())->toBe("\033[15~"); // F5
    });

    test('does not create spurious key events from mouse sequences', function () {
        $terminal = new FakeTerminal();
        
        // Queue a mouse sequence followed by a regular key
        $terminal->queueInput("\033[<0;10;5M", "a");
        
        $first = $terminal->readKey();
        $second = $terminal->readKey();
        
        expect($first)->toBe("\033[<0;10;5M"); // Complete mouse sequence
        expect($second)->toBe("a");            // Regular key
        expect($terminal->readKey())->toBeNull(); // No extra keys
    });
});