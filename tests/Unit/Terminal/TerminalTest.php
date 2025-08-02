<?php

declare(strict_types=1);

use Crumbls\Tui\Terminal\Terminal;
use Crumbls\Tui\Terminal\Size;

describe('Terminal', function () {
    test('gets terminal size', function () {
        $terminal = new Terminal();
        $size = $terminal->getSize();
        
        expect($size)->toBeInstanceOf(Size::class);
        expect($size->width)->toBeGreaterThan(0);
        expect($size->height)->toBeGreaterThan(0);
    });

    test('handles color support detection', function () {
        $terminal = new Terminal();
        
        // This will depend on the actual environment, but should not throw
        $supportsColors = $terminal->supportsColors();
        expect($supportsColors)->toBeBool();
    });

    test('handles mouse support detection', function () {
        $terminal = new Terminal();
        
        // This will depend on the actual environment, but should not throw
        $supportsMouse = $terminal->supportsMouse();
        expect($supportsMouse)->toBeBool();
    });

    test('can enable and disable raw mode without errors', function () {
        $terminal = new Terminal();
        
        // These should execute without throwing exceptions
        $terminal->enableRawMode();
        $terminal->disableRawMode();
        
        // Multiple calls should be safe
        $terminal->enableRawMode();
        $terminal->enableRawMode();
        $terminal->disableRawMode();
        $terminal->disableRawMode();
        
        expect(true)->toBeTrue(); // If we get here, no exceptions were thrown
    });

    test('write method does not throw', function () {
        $terminal = new Terminal();
        
        // We can't easily test the output, but we can ensure it doesn't crash
        $terminal->write('');
        $terminal->write('test');
        $terminal->write("multi\nline\ntext");
        
        expect(true)->toBeTrue(); // If we get here, no exceptions were thrown
    });

    test('clear method does not throw', function () {
        $terminal = new Terminal();
        
        $terminal->clear();
        
        expect(true)->toBeTrue(); // If we get here, no exceptions were thrown
    });

    test('readKey with timeout returns null when no input', function () {
        $terminal = new Terminal();
        
        // With a very short timeout, should return null quickly
        $result = $terminal->readKey(0.001);
        expect($result)->toBeNull();
    });

    test('destructor cleans up properly', function () {
        $terminal = new Terminal();
        $terminal->enableRawMode();
        
        // When terminal goes out of scope, destructor should clean up
        // We can't easily test this, but we can ensure creating/destroying doesn't crash
        unset($terminal);
        
        expect(true)->toBeTrue(); // If we get here, destructor didn't crash
    });
});