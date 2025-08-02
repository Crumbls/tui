<?php

declare(strict_types=1);

use Crumbls\Tui\MainLoop;
use Crumbls\Tui\Events\UserActivityEvent;
use Crumbls\Tui\Events\EventBus;

require_once __DIR__ . '/helpers/private_property_helpers.php';

describe('MainLoop idle detection with real EventBus', function () {
    test('loop switches to idle after threshold and returns to active on event', function () {
        $eventBus = new EventBus();
        $loop = new MainLoop();
        $loop->setEventBus($eventBus)->setIdleThreshold(1)->setIdleTickRate(1)->setTickRate(1000);
        // Set lastActivity to now to ensure not idle
        set_private_property($loop, 'lastActivity', microtime(true));
        $loop->tick();
        expect(get_private_property($loop, 'isIdle'))->toBeFalse();
        // Simulate time passing with no activity
        set_private_property($loop, 'lastActivity', microtime(true) - 2);
        $loop->tick();
        expect(get_private_property($loop, 'isIdle'))->toBeTrue();
        // Simulate user activity event
        $eventBus->emit(new UserActivityEvent());
        $loop->tick();
        expect(get_private_property($loop, 'isIdle'))->toBeFalse();
    });
});
