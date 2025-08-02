<?php

declare(strict_types=1);

use Crumbls\Tui\Terminal\Terminal;

describe('Terminal compatibility', function () {
    test('supports colors for most terminal types', function () {
        $terminal = new Terminal();
        
        // Test various terminal types by mocking $_ENV['TERM']
        $testCases = [
            // Should support colors
            ['xterm-256color', true],
            ['xterm-color', true],
            ['screen-256color', true],
            ['tmux-256color', true],
            ['alacritty', true],
            ['kitty', true],
            ['wezterm', true],
            ['iterm2', true],
            ['gnome-terminal', true],
            
            // Should not support colors
            ['dumb', false],
            ['unknown', false],
            ['', false],
        ];
        
        foreach ($testCases as [$termType, $shouldSupport]) {
            // Temporarily set TERM
            $originalTerm = $_ENV['TERM'] ?? null;
            $originalColorTerm = $_ENV['COLORTERM'] ?? null;
            
            $_ENV['TERM'] = $termType;
            unset($_ENV['COLORTERM']);
            
            $result = $terminal->supportsColors();
            expect($result)->toBe($shouldSupport, "Terminal '$termType' color support should be " . ($shouldSupport ? 'true' : 'false'));
            
            // Restore
            if ($originalTerm !== null) {
                $_ENV['TERM'] = $originalTerm;
            } else {
                unset($_ENV['TERM']);
            }
            if ($originalColorTerm !== null) {
                $_ENV['COLORTERM'] = $originalColorTerm;  
            }
        }
    });

    test('supports mouse for most terminal types', function () {
        $terminal = new Terminal();
        
        // Mock posix functions to return true (interactive terminal)
        if (!function_exists('posix_isatty')) {
            // Define stub if not available
            function posix_isatty($fd) { return true; }
        }
        
        $testCases = [
            // Should support mouse
            ['xterm-256color', true],
            ['xterm', true],
            ['screen', true],
            ['tmux-256color', true],
            ['alacritty', true],
            ['kitty', true],
            ['wezterm', true],
            ['iterm2', true],
            ['gnome-terminal', true],
            ['konsole', true],
            ['anything-else', true], // Default to true for unknown terminals
            
            // Should not support mouse
            ['dumb', false],
            ['unknown', false],
            ['', false],
        ];
        
        foreach ($testCases as [$termType, $shouldSupport]) {
            // Temporarily set TERM
            $originalTerm = $_ENV['TERM'] ?? null;
            $_ENV['TERM'] = $termType;
            
            $result = $terminal->supportsMouse();
            expect($result)->toBe($shouldSupport, "Terminal '$termType' mouse support should be " . ($shouldSupport ? 'true' : 'false'));
            
            // Restore
            if ($originalTerm !== null) {
                $_ENV['TERM'] = $originalTerm;
            } else {
                unset($_ENV['TERM']);
            }
        }
    });

    test('COLORTERM environment variable enables color support', function () {
        $terminal = new Terminal();
        
        // Set up test environment
        $originalTerm = $_ENV['TERM'] ?? null;
        $originalColorTerm = $_ENV['COLORTERM'] ?? null;
        
        $_ENV['TERM'] = 'basic-terminal'; // No explicit color support
        $_ENV['COLORTERM'] = 'truecolor';
        
        expect($terminal->supportsColors())->toBeTrue();
        
        // Clean up
        if ($originalTerm !== null) {
            $_ENV['TERM'] = $originalTerm;
        } else {
            unset($_ENV['TERM']);
        }
        if ($originalColorTerm !== null) {
            $_ENV['COLORTERM'] = $originalColorTerm;
        } else {
            unset($_ENV['COLORTERM']);
        }
    });

    test('defaults to supporting features for unknown terminals', function () {
        $terminal = new Terminal();
        
        $originalTerm = $_ENV['TERM'] ?? null;
        $_ENV['TERM'] = 'some-new-fancy-terminal-2025';
        
        // Should default to supporting both colors and mouse
        expect($terminal->supportsColors())->toBeTrue();
        expect($terminal->supportsMouse())->toBeTrue();
        
        // Restore
        if ($originalTerm !== null) {
            $_ENV['TERM'] = $originalTerm;
        } else {
            unset($_ENV['TERM']);
        }
    });
});