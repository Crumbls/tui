<?php

declare(strict_types=1);

use Crumbls\Tui\Terminal\SafeTerminal;
use Crumbls\Tui\Terminal\Size;
use Crumbls\Tui\ErrorHandling\ErrorHandler;
use Crumbls\Tui\Testing\FakeTerminal;

describe('SafeTerminal', function () {
    test('wraps terminal operations with error handling', function () {
        $terminal = new FakeTerminal();
        $errorHandler = new ErrorHandler($terminal, false);
        $safeTerminal = new SafeTerminal($terminal, $errorHandler);
        
        // Normal operations should work
        expect($safeTerminal->getSize())->toEqual(new Size(80, 24));
        expect($safeTerminal->supportsColors())->toBeTrue();
        expect($safeTerminal->supportsMouse())->toBeTrue();
        
        $safeTerminal->write("test");
        expect($terminal->getOutputAsString())->toContain("test");
    });

    test('provides fallback for size detection failures', function () {
        // Create a terminal that will fail size detection
        $failingTerminal = new class implements \Crumbls\Tui\Contracts\TerminalInterface {
            public function getSize(): Size {
                throw new Exception("Size detection failed");
            }
            public function readKey(float $timeout = 0): ?string { return null; }
            public function write(string $content): void {}
            public function clear(): void {}
            public function enableRawMode(): void {}
            public function disableRawMode(): void {}
            public function supportsColors(): bool { return false; }
            public function supportsMouse(): bool { return false; }
            public function enableMouseReporting(): void {}
            public function disableMouseReporting(): void {}
            public function queue(string $command): void {}
            public function flush(): void {}
            public function moveCursor(int $x, int $y): void {}
            public function getCursorPosition(): ?array { return null; }
            public function setForegroundColor(int $r, int $g, int $b): void {}
            public function setBackgroundColor(int $r, int $g, int $b): void {}
            public function resetColors(): void {}
        };
        
        $errorHandler = new ErrorHandler(null, false);
        $safeTerminal = new SafeTerminal($failingTerminal, $errorHandler);
        
        // Should return default size instead of throwing
        $size = $safeTerminal->getSize();
        expect($size)->toEqual(new Size(80, 24));
        
        // Should have logged the error
        $stats = $errorHandler->getErrorStats();
        expect($stats['total_errors'])->toBe(1);
    });

    test('handles read failures gracefully', function () {
        $failingTerminal = new class implements \Crumbls\Tui\Contracts\TerminalInterface {
            public function getSize(): Size { return new Size(80, 24); }
            public function readKey(float $timeout = 0): ?string { 
                throw new Exception("Read failed");
            }
            public function write(string $content): void {}
            public function clear(): void {}
            public function enableRawMode(): void {}
            public function disableRawMode(): void {}
            public function supportsColors(): bool { return false; }
            public function supportsMouse(): bool { return false; }
            public function enableMouseReporting(): void {}
            public function disableMouseReporting(): void {}
            public function queue(string $command): void {}
            public function flush(): void {}
            public function moveCursor(int $x, int $y): void {}
            public function getCursorPosition(): ?array { return null; }
            public function setForegroundColor(int $r, int $g, int $b): void {}
            public function setBackgroundColor(int $r, int $g, int $b): void {}
            public function resetColors(): void {}
        };
        
        $errorHandler = new ErrorHandler(null, false);
        $safeTerminal = new SafeTerminal($failingTerminal, $errorHandler);
        
        // Should return null instead of throwing
        expect($safeTerminal->readKey())->toBeNull();
        
        $stats = $errorHandler->getErrorStats();
        expect($stats['total_errors'])->toBe(1);
    });

    test('handles write failures gracefully', function () {
        $failingTerminal = new class implements \Crumbls\Tui\Contracts\TerminalInterface {
            public function getSize(): Size { return new Size(80, 24); }
            public function readKey(float $timeout = 0): ?string { return null; }
            public function write(string $content): void { 
                throw new Exception("Write failed"); 
            }
            public function clear(): void {}
            public function enableRawMode(): void {}
            public function disableRawMode(): void {}
            public function supportsColors(): bool { return false; }
            public function supportsMouse(): bool { return false; }
            public function enableMouseReporting(): void {}
            public function disableMouseReporting(): void {}
            public function queue(string $command): void {}
            public function flush(): void {}
            public function moveCursor(int $x, int $y): void {}
            public function getCursorPosition(): ?array { return null; }
            public function setForegroundColor(int $r, int $g, int $b): void {}
            public function setBackgroundColor(int $r, int $g, int $b): void {}
            public function resetColors(): void {}
        };
        
        $errorHandler = new ErrorHandler(null, false);
        $safeTerminal = new SafeTerminal($failingTerminal, $errorHandler);
        
        // Should not throw
        $safeTerminal->write("test");
        
        $stats = $errorHandler->getErrorStats();
        expect($stats['total_errors'])->toBe(1);
    });

    test('provides access to underlying terminal', function () {
        $terminal = new FakeTerminal();
        $errorHandler = new ErrorHandler($terminal, false);
        $safeTerminal = new SafeTerminal($terminal, $errorHandler);
        
        expect($safeTerminal->getUnderlying())->toBe($terminal);
    });
});