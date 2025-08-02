<?php

declare(strict_types=1);

use Crumbls\Tui\ErrorHandling\ErrorHandler;
use Crumbls\Tui\Exceptions\TerminalException;
use Crumbls\Tui\Exceptions\RenderException;
use Crumbls\Tui\Exceptions\TuiException;
use Crumbls\Tui\Testing\FakeTerminal;

describe('ErrorHandler', function () {
    test('handles recoverable terminal errors gracefully', function () {
        $terminal = new FakeTerminal();
        $handler = new ErrorHandler($terminal, false); // Don't log during tests
        
        $error = TerminalException::sizeDetectionFailed();
        expect($error->isRecoverable())->toBeTrue();
        
        // Should not throw since it's recoverable
        $handler->handleTerminalError($error);
        expect($handler->shouldContinue($error))->toBeTrue();
        
        $stats = $handler->getErrorStats();
        expect($stats['total_errors'])->toBe(1);
        expect($stats['error_counts']['terminal'])->toBe(1);
    });

    test('re-throws fatal terminal errors after cleanup', function () {
        $terminal = new FakeTerminal();
        $handler = new ErrorHandler($terminal, false);
        
        $error = TerminalException::initializationFailed("test failure");
        expect($error->isRecoverable())->toBeFalse();
        
        expect(fn() => $handler->handleTerminalError($error))
            ->toThrow(TerminalException::class);
    });

    test('handles render errors without throwing', function () {
        $handler = new ErrorHandler(null, false);
        
        $error = RenderException::widgetRenderFailed("TestWidget");
        
        // Should not throw - render errors are recoverable
        $handler->handleRenderError($error);
        
        $stats = $handler->getErrorStats();
        expect($stats['total_errors'])->toBe(1);
        expect($stats['error_counts']['render'])->toBe(1);
    });

    test('treats unexpected errors as fatal', function () {
        $handler = new ErrorHandler(null, false);
        
        $error = new Exception("Unexpected error");
        expect($handler->shouldContinue($error))->toBeFalse();
        
        expect(fn() => $handler->handleUnexpectedError($error))
            ->toThrow(Exception::class);
    });

    test('tracks error statistics', function () {
        $handler = new ErrorHandler(null, false);
        
        // Add various error types
        $handler->handleRenderError(RenderException::bufferFailed("test"));
        $handler->handleTuiError(TuiException::recoverable("test"));
        $handler->handleRenderError(RenderException::screenUpdateFailed());
        
        $stats = $handler->getErrorStats();
        expect($stats['total_errors'])->toBe(3);
        expect($stats['error_counts']['render'])->toBe(2);
        expect($stats['error_counts']['tui'])->toBe(1);
        expect(count($stats['recent_errors']))->toBe(3);
    });

    test('limits error history size', function () {
        $handler = new ErrorHandler(null, false);
        
        // Add more than 50 errors
        for ($i = 0; $i < 60; $i++) {
            $handler->handleRenderError(RenderException::bufferFailed("error $i"));
        }
        
        $stats = $handler->getErrorStats();
        expect($stats['total_errors'])->toBe(50); // Should cap at 50
    });

    test('registers and executes cleanup handlers', function () {
        $handler = new ErrorHandler(null, false);
        $cleanupCalled = false;
        
        $handler->registerCleanupHandler(function () use (&$cleanupCalled) {
            $cleanupCalled = true;
        });
        
        $handler->emergencyCleanup();
        expect($cleanupCalled)->toBeTrue();
    });

    test('emergency cleanup is safe to call multiple times', function () {
        $handler = new ErrorHandler(null, false);
        $cleanupCount = 0;
        
        $handler->registerCleanupHandler(function () use (&$cleanupCount) {
            $cleanupCount++;
        });
        
        $handler->emergencyCleanup();
        $handler->emergencyCleanup();
        $handler->emergencyCleanup();
        
        // Should only run cleanup once
        expect($cleanupCount)->toBe(1);
    });

    test('handles cleanup handler exceptions gracefully', function () {
        $handler = new ErrorHandler(null, false);
        
        $handler->registerCleanupHandler(function () {
            throw new Exception("Cleanup failed");
        });
        
        // Should not throw even if cleanup handlers fail
        $handler->emergencyCleanup();
        
        $stats = $handler->getErrorStats();
        expect($stats['emergency_mode'])->toBeTrue();
    });
});