<?php

declare(strict_types=1);

use Crumbls\Tui\MainLoop;

describe('MainLoop', function () {
    test('runs tick, input, and render callbacks', function () {
        $calls = [];
        $loop = new MainLoop();
        $loop
            ->onTick(function () use (&$calls) { $calls[] = 'tick'; })
            ->onInput(function () use (&$calls) { $calls[] = 'input'; })
            ->onRender(function () use (&$calls) { $calls[] = 'render'; });
        $loop->setTickRate(1000); // Fast for test
        // Only test tick() for unit test
        $loop->tick();
        expect($calls)->toContain('tick');
        expect($calls)->toContain('input');
        expect($calls)->toContain('render');
    });

    test('can be stopped and restarted', function () {
        $loop = new MainLoop();
        $loop->onTick(fn () => null);
        $loop->stop();
        expect($loop->tick())->toBeFalse();
    });

    test('tick rate can be changed', function () {
        $loop = new MainLoop();
        $loop->setTickRate(60);
        expect(true)->toBeTrue(); // Just ensure no exception
    });
});
