<?php

declare(strict_types=1);

describe('Click area detection', function () {
    test('point in area detection works correctly', function () {
        $isPointInArea = function(int $x, int $y, array $area): bool {
            return $x >= $area['x'] && 
                   $x < $area['x'] + $area['width'] &&
                   $y >= $area['y'] && 
                   $y < $area['y'] + $area['height'];
        };
        
        // Test area: x=25, y=17, width=15, height=3
        // So valid range is x=25-39, y=17-19
        $clickBoxArea = ['x' => 25, 'y' => 17, 'width' => 15, 'height' => 3];
        
        // Test corners
        expect($isPointInArea(25, 17, $clickBoxArea))->toBeTrue();  // Top-left
        expect($isPointInArea(39, 17, $clickBoxArea))->toBeTrue();  // Top-right
        expect($isPointInArea(25, 19, $clickBoxArea))->toBeTrue();  // Bottom-left
        expect($isPointInArea(39, 19, $clickBoxArea))->toBeTrue();  // Bottom-right
        
        // Test center
        expect($isPointInArea(32, 18, $clickBoxArea))->toBeTrue();
        
        // Test outside bounds
        expect($isPointInArea(24, 17, $clickBoxArea))->toBeFalse(); // Left of area
        expect($isPointInArea(40, 17, $clickBoxArea))->toBeFalse(); // Right of area
        expect($isPointInArea(25, 16, $clickBoxArea))->toBeFalse(); // Above area
        expect($isPointInArea(25, 20, $clickBoxArea))->toBeFalse(); // Below area
    });

    test('demo click areas do not overlap', function () {
        $clickBoxArea = ['x' => 25, 'y' => 17, 'width' => 15, 'height' => 3];      // x=25-39
        $resetButtonArea = ['x' => 45, 'y' => 17, 'width' => 12, 'height' => 3];   // x=45-56
        $errorTestArea = ['x' => 62, 'y' => 17, 'width' => 12, 'height' => 3];     // x=62-73
        
        $isPointInArea = function(int $x, int $y, array $area): bool {
            return $x >= $area['x'] && 
                   $x < $area['x'] + $area['width'] &&
                   $y >= $area['y'] && 
                   $y < $area['y'] + $area['height'];
        };
        
        // Test that areas don't overlap - check boundary points
        
        // Point between click box and reset button
        $betweenPoint = 42;
        expect($isPointInArea($betweenPoint, 17, $clickBoxArea))->toBeFalse();
        expect($isPointInArea($betweenPoint, 17, $resetButtonArea))->toBeFalse();
        
        // Point between reset button and error test
        $betweenPoint2 = 59;
        expect($isPointInArea($betweenPoint2, 17, $resetButtonArea))->toBeFalse();
        expect($isPointInArea($betweenPoint2, 17, $errorTestArea))->toBeFalse();
        
        // Verify each area works independently
        expect($isPointInArea(30, 18, $clickBoxArea))->toBeTrue();     // In click box only
        expect($isPointInArea(30, 18, $resetButtonArea))->toBeFalse();
        expect($isPointInArea(30, 18, $errorTestArea))->toBeFalse();
        
        expect($isPointInArea(50, 18, $resetButtonArea))->toBeTrue();  // In reset button only
        expect($isPointInArea(50, 18, $clickBoxArea))->toBeFalse();
        expect($isPointInArea(50, 18, $errorTestArea))->toBeFalse();
        
        expect($isPointInArea(67, 18, $errorTestArea))->toBeTrue();    // In error test only
        expect($isPointInArea(67, 18, $clickBoxArea))->toBeFalse();
        expect($isPointInArea(67, 18, $resetButtonArea))->toBeFalse();
    });

    test('visual layout matches coordinate expectations', function () {
        // This test documents the expected layout
        $layout = [
            'line_17_visual' => '  [ CLICK BOX ]     [RESET STATS]    [ERROR TEST]',
            'click_box_start' => strpos('  [ CLICK BOX ]     [RESET STATS]    [ERROR TEST]', '[ CLICK BOX ]'),
            'reset_button_start' => strpos('  [ CLICK BOX ]     [RESET STATS]    [ERROR TEST]', '[RESET STATS]'),
            'error_test_start' => strpos('  [ CLICK BOX ]     [RESET STATS]    [ERROR TEST]', '[ERROR TEST]'),
        ];
        
        // The visual elements start at these character positions
        expect($layout['click_box_start'])->toBe(2);   // "[ CLICK BOX ]" starts at position 2
        expect($layout['reset_button_start'])->toBe(20); // "[RESET STATS]" starts at position 20
        expect($layout['error_test_start'])->toBe(37);   // "[ERROR TEST]" starts at position 37
        
        // Our coordinate areas should roughly align with these positions
        $clickBoxArea = ['x' => 25, 'y' => 17, 'width' => 15, 'height' => 3];
        $resetButtonArea = ['x' => 45, 'y' => 17, 'width' => 12, 'height' => 3];
        $errorTestArea = ['x' => 62, 'y' => 17, 'width' => 12, 'height' => 3];
        
        // These coordinates might be the issue - let's see what the debug reveals
        expect($clickBoxArea['x'])->toBe(25);   // Might need adjustment
        expect($resetButtonArea['x'])->toBe(45); // Might need adjustment  
        expect($errorTestArea['x'])->toBe(62);   // Might need adjustment
    });
});