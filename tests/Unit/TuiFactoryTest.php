<?php

declare(strict_types=1);

use Crumbls\Tui\TuiFactory;
use Crumbls\Tui\Terminal\SafeTerminal;
use Crumbls\Tui\ErrorHandling\ErrorHandler;
use Crumbls\Tui\Events\EventBus;
use Crumbls\Tui\Input\InputHandler;
use Crumbls\Tui\Rendering\SimpleRenderer;
use Crumbls\Tui\MainLoop;

describe('TuiFactory', function () {
    test('creates bulletproof TUI components', function () {
        $factory = TuiFactory::create();
        
        $terminal = $factory->terminal();
        $errorHandler = $factory->errorHandler();
        $eventBus = $factory->eventBus();
        $inputHandler = $factory->inputHandler();
        $renderer = $factory->renderer();
        
        expect($terminal)->toBeInstanceOf(SafeTerminal::class);
        expect($errorHandler)->toBeInstanceOf(ErrorHandler::class);
        expect($eventBus)->toBeInstanceOf(EventBus::class);
        expect($inputHandler)->toBeInstanceOf(InputHandler::class);
        expect($renderer)->toBeInstanceOf(SimpleRenderer::class);
    });

    test('creates main loop with error handling', function () {
        $factory = TuiFactory::create();
        $loop = $factory->mainLoop(enableMouse: true);
        
        expect($loop)->toBeInstanceOf(MainLoop::class);
    });

    test('demo method returns complete stack', function () {
        $factory = TuiFactory::create();
        $components = $factory->demo();
        
        expect($components)->toHaveKey('terminal');
        expect($components)->toHaveKey('eventBus');
        expect($components)->toHaveKey('inputHandler');
        expect($components)->toHaveKey('renderer');
        expect($components)->toHaveKey('errorHandler');
        expect($components)->toHaveKey('loop');
        
        expect($components['terminal'])->toBeInstanceOf(SafeTerminal::class);
        expect($components['errorHandler'])->toBeInstanceOf(ErrorHandler::class);
        expect($components['inputHandler']->isMouseEnabled())->toBeTrue();
    });

    test('reuses terminal and error handler instances', function () {
        $factory = TuiFactory::create();
        
        $terminal1 = $factory->terminal();
        $terminal2 = $factory->terminal();
        $errorHandler1 = $factory->errorHandler();
        $errorHandler2 = $factory->errorHandler();
        
        expect($terminal1)->toBe($terminal2);
        expect($errorHandler1)->toBe($errorHandler2);
    });

    test('creates new event bus instances', function () {
        $factory = TuiFactory::create();
        
        $eventBus1 = $factory->eventBus();
        $eventBus2 = $factory->eventBus();
        
        // Each call should create a new instance
        expect($eventBus1)->not->toBe($eventBus2);
    });
});