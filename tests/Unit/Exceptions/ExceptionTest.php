<?php

declare(strict_types=1);

use Crumbls\Tui\Exceptions\TuiException;
use Crumbls\Tui\Exceptions\TerminalException;
use Crumbls\Tui\Exceptions\RenderException;
use Crumbls\Tui\Exceptions\InputException;

describe('TUI Exceptions', function () {
    test('TuiException handles recoverable flag', function () {
        $recoverable = TuiException::recoverable("Test error");
        expect($recoverable->isRecoverable())->toBeTrue();
        
        $fatal = TuiException::fatal("Fatal error");
        expect($fatal->isRecoverable())->toBeFalse();
    });

    test('TerminalException creates proper error types', function () {
        $initError = TerminalException::initializationFailed("test");
        expect($initError->isRecoverable())->toBeFalse();
        expect($initError->getCode())->toBe(1001);
        
        $sizeError = TerminalException::sizeDetectionFailed();
        expect($sizeError->isRecoverable())->toBeTrue();
        expect($sizeError->getCode())->toBe(1002);
        
        $readError = TerminalException::readFailed("timeout");
        expect($readError->isRecoverable())->toBeTrue();
        expect($readError->getCode())->toBe(1003);
    });

    test('RenderException creates proper error types', function () {
        $bufferError = RenderException::bufferFailed("clear");
        expect($bufferError->isRecoverable())->toBeTrue();
        expect($bufferError->getCode())->toBe(2001);
        
        $widgetError = RenderException::widgetRenderFailed("Button");
        expect($widgetError->isRecoverable())->toBeTrue();
        expect($widgetError->getCode())->toBe(2002);
        
        $dimensionError = RenderException::invalidDimensions(-1, 0);
        expect($dimensionError->isRecoverable())->toBeTrue();
        expect($dimensionError->getMessage())->toContain("-1x0");
    });

    test('InputException creates proper error types', function () {
        $parseError = InputException::parseFailed("\033[invalid");
        expect($parseError->isRecoverable())->toBeTrue();
        expect($parseError->getCode())->toBe(3001);
        expect($parseError->getMessage())->toContain(bin2hex("\033[invalid"));
        
        $handlerError = InputException::handlerInitFailed("no terminal");
        expect($handlerError->isRecoverable())->toBeFalse();
        expect($handlerError->getCode())->toBe(3002);
        
        $timeoutError = InputException::processingTimeout(5.0);
        expect($timeoutError->isRecoverable())->toBeTrue();
        expect($timeoutError->getCode())->toBe(3003);
        expect($timeoutError->getMessage())->toContain("5s");
    });

    test('exceptions preserve previous exception chain', function () {
        $original = new Exception("Original error");
        $terminal = TerminalException::readFailed("wrapped", $original);
        
        expect($terminal->getPrevious())->toBe($original);
    });
});