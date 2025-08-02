<?php

declare(strict_types=1);

use Crumbls\Tui\Input\InputHandler;
use Crumbls\Tui\Testing\FakeTerminal;
use Crumbls\Tui\Events\EventBus;

describe('Demo mouse integration', function () {
    test('mouse events work with input handler and event bus', function () {
        $terminal = new FakeTerminal();
        $eventBus = new EventBus();
        $handler = new InputHandler($terminal, $eventBus);
        
        // Enable mouse support
        $handler->setMouseEnabled(true);
        
        // Simulate mouse events like in demo
        $terminal->queueMouseClick(25, 17, 'left', 'press'); // Click box area
        $terminal->queueMouseClick(45, 17, 'left', 'press'); // Reset button area
        $terminal->queueMouseScroll(30, 10, 'up');
        
        // Process all events
        expect($handler->processInput())->toBeTrue();
        expect($handler->processInput())->toBeTrue();
        expect($handler->processInput())->toBeTrue();
        
        // Check events were captured
        $events = $eventBus->query()->whereType('MouseInputEvent')->get();
        expect(count($events))->toBe(3);
        
        // Verify first event (click box)
        expect($events[0]->getX())->toBe(25);
        expect($events[0]->getY())->toBe(17);
        expect($events[0]->getButton())->toBe('left');
        expect($events[0]->getAction())->toBe('press');
        
        // Verify second event (reset button)
        expect($events[1]->getX())->toBe(45);
        expect($events[1]->getY())->toBe(17);
        expect($events[1]->getButton())->toBe('left');
        expect($events[1]->getAction())->toBe('press');
        
        // Verify third event (scroll)
        expect($events[2]->getX())->toBe(30);
        expect($events[2]->getY())->toBe(10);
        expect($events[2]->getButton())->toBe('up');
        expect($events[2]->getAction())->toBe('scroll');
    });

    test('terminal mouse reporting can be enabled and disabled', function () {
        $terminal = new FakeTerminal();
        
        $terminal->enableMouseReporting();
        expect($terminal->getOutput())->toContain('MOUSE_REPORTING_ENABLED');
        
        $terminal->disableMouseReporting();
        expect($terminal->getOutput())->toContain('MOUSE_REPORTING_DISABLED');
    });

    test('demo area detection logic works correctly', function () {
        // Simulate the demo's isPointInArea logic
        $clickBoxArea = ['x' => 25, 'y' => 17, 'width' => 15, 'height' => 3];
        $resetButtonArea = ['x' => 45, 'y' => 17, 'width' => 12, 'height' => 3];
        
        $isPointInArea = function(int $x, int $y, array $area): bool {
            return $x >= $area['x'] && 
                   $x < $area['x'] + $area['width'] &&
                   $y >= $area['y'] && 
                   $y < $area['y'] + $area['height'];
        };
        
        // Test click box area
        expect($isPointInArea(25, 17, $clickBoxArea))->toBeTrue(); // Top-left
        expect($isPointInArea(39, 19, $clickBoxArea))->toBeTrue(); // Bottom-right
        expect($isPointInArea(24, 17, $clickBoxArea))->toBeFalse(); // Outside left
        expect($isPointInArea(40, 17, $clickBoxArea))->toBeFalse(); // Outside right
        
        // Test reset button area  
        expect($isPointInArea(45, 17, $resetButtonArea))->toBeTrue(); // Top-left
        expect($isPointInArea(56, 19, $resetButtonArea))->toBeTrue(); // Bottom-right
        expect($isPointInArea(44, 17, $resetButtonArea))->toBeFalse(); // Outside left
        expect($isPointInArea(57, 17, $resetButtonArea))->toBeFalse(); // Outside right
        
        // Test areas don't overlap
        expect($isPointInArea(42, 17, $clickBoxArea))->toBeFalse();
        expect($isPointInArea(42, 17, $resetButtonArea))->toBeFalse();
    });
});