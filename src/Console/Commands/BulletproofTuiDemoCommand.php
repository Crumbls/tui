<?php

declare(strict_types=1);

namespace Crumbls\Tui\Console\Commands;

use Illuminate\Console\Command;
use Crumbls\Tui\TuiFactory;
use Crumbls\Tui\Events\UserActivityEvent;
use Crumbls\Tui\Events\KeyInputEvent;
use Crumbls\Tui\Events\MouseInputEvent;
use Crumbls\Tui\Exceptions\TuiException;

class BulletproofTuiDemoCommand extends Command
{
    protected $signature = 'tui:bulletproof-demo';
    protected $description = 'Demo the bulletproof TUI with comprehensive error handling';

    public function handle()
    {
        $this->info('Starting Bulletproof TUI Demo...');
        
        try {
            // Create bulletproof TUI stack
            $factory = TuiFactory::create();
            $components = $factory->demo();
            
            [
                'terminal' => $terminal,
                'eventBus' => $eventBus,
                'inputHandler' => $inputHandler,
                'renderer' => $renderer,
                'errorHandler' => $errorHandler,
                'loop' => $loop,
            ] = $components;
            
            // Demo state with error tracking
            $stats = [
                'tick' => 0,
                'keyPresses' => 0,
                'mousePresses' => 0,
                'errors' => 0,
                'lastKey' => 'none',
                'lastMouse' => 'none',
                'events' => [],
                'clickBoxClicks' => 0,
                'resetButtonClicks' => 0,
                'errorTestClicks' => 0,
            ];

            // Define clickable areas including error test area
            $clickBoxArea = ['x' => 25, 'y' => 17, 'width' => 15, 'height' => 3];
            $resetButtonArea = ['x' => 45, 'y' => 17, 'width' => 12, 'height' => 3];
            $errorTestArea = ['x' => 62, 'y' => 17, 'width' => 12, 'height' => 3];
            
            // Listen for key events
            $eventBus->listen('KeyInputEvent', function (KeyInputEvent $event) use (&$stats, $eventBus) {
                $stats['keyPresses']++;
                $stats['lastKey'] = $event->getKey();
                
                // Build detailed event description
                $eventDesc = $event->getKey();
                if (!empty($event->getModifiers())) {
                    $eventDesc = implode('+', $event->getModifiers()) . '+' . $event->getKey();
                }
                if ($event->isSpecialKey()) {
                    $eventDesc = '[' . $eventDesc . ']';
                }
                
                $stats['events'][] = $eventDesc . ' (raw: ' . bin2hex($event->getRawInput()) . ')';
                
                // Keep only last 6 events (room for error info)
                if (count($stats['events']) > 6) {
                    array_shift($stats['events']);
                }
                
                // Store quit request
                if ($event->getKey() === 'q') {
                    $GLOBALS['shouldQuit'] = true;
                }
                
                $eventBus->emit(new UserActivityEvent());
            });

            // Listen for mouse events
            $eventBus->listen('MouseInputEvent', function (MouseInputEvent $event) use (&$stats, $eventBus, $clickBoxArea, $resetButtonArea, $errorTestArea, $errorHandler) {
                $stats['mousePresses']++;
                
                $mouseDesc = "{$event->getAction()} {$event->getButton()} at ({$event->getX()},{$event->getY()})";
                if (!empty($event->getModifiers())) {
                    $mouseDesc = implode('+', $event->getModifiers()) . '+' . $mouseDesc;
                }
                $stats['lastMouse'] = $mouseDesc;
                
                $stats['events'][] = $mouseDesc . ' (raw: ' . bin2hex($event->getRawInput()) . ')';
                
                // Check clickable areas
                if ($event->getAction() === 'press' && $event->getButton() === 'left') {
                    if ($this->isPointInArea($event->getX(), $event->getY(), $clickBoxArea)) {
                        $stats['clickBoxClicks']++;
                        $stats['events'][] = '*** CLICK BOX HIT! ***';
                    }
                    
                    if ($this->isPointInArea($event->getX(), $event->getY(), $resetButtonArea)) {
                        $stats['resetButtonClicks']++;
                        $stats['events'][] = '*** RESET STATS! ***';
                        $stats['clickBoxClicks'] = 0;
                        $stats['keyPresses'] = 0;
                        $stats['mousePresses'] = 1;
                        $stats['errors'] = 0;
                        $stats['errorTestClicks'] = 0;
                    }
                    
                    // Error test area - deliberately trigger recoverable error
                    if ($this->isPointInArea($event->getX(), $event->getY(), $errorTestArea)) {
                        $stats['errorTestClicks']++;
                        $stats['events'][] = '*** ERROR TEST! ***';
                        
                        // Trigger a recoverable error to demonstrate error handling
                        try {
                            throw TuiException::recoverable("Test error triggered by user click");
                        } catch (TuiException $e) {
                            $errorHandler->handleTuiError($e);
                            $stats['errors']++;
                        }
                    }
                }
                
                // Keep only last 6 events
                if (count($stats['events']) > 6) {
                    array_shift($stats['events']);
                }
                
                // Right-click to quit
                if ($event->getAction() === 'press' && $event->getButton() === 'right') {
                    $GLOBALS['shouldQuit'] = true;
                }
                
                $eventBus->emit(new UserActivityEvent());
            });
            
            // Configure loop callbacks
            $loop->onTick(function () use (&$stats, $loop, $renderer, $terminal, $eventBus, $errorHandler) {
                $stats['tick']++;
                
                // Check for quit request
                if ($GLOBALS['shouldQuit'] ?? false) {
                    $loop->stop();
                    return;
                }
                
                // Get terminal size safely
                $size = $terminal->getSize();
                $renderer->setSize($size->width, $size->height);
                
                // Get idle stats and error stats
                $isIdle = (new \ReflectionProperty($loop, 'isIdle'))->getValue($loop);
                $lastActivity = (new \ReflectionProperty($loop, 'lastActivity'))->getValue($loop);
                $currentTime = microtime(true);
                $timeSinceActivity = $currentTime - $lastActivity;
                $errorStats = $errorHandler->getErrorStats();
                
                // Build content with error information
                $content = [
                    'Bulletproof TUI Demo - Error Handling Active',
                    'Tick: ' . $stats['tick'] . ' | Keys: ' . $stats['keyPresses'] . ' | Mouse: ' . $stats['mousePresses'],
                    'Errors Handled: ' . $stats['errors'] . ' | Total System Errors: ' . $errorStats['total_errors'],
                    'Last Key: ' . $stats['lastKey'],
                    'Last Mouse: ' . $stats['lastMouse'],
                    'Idle: ' . ($isIdle ? 'yes' : 'no') . ' | Activity: ' . number_format($timeSinceActivity, 2) . 's ago',
                    '',
                    'Recent Events (Key + Mouse + Errors):',
                ];
                
                foreach ($stats['events'] as $event) {
                    $content[] = '  ' . $event;
                }
                
                $content[] = '';
                $content[] = 'Error Handler Stats:';
                $content[] = '  Emergency Mode: ' . ($errorStats['emergency_mode'] ? 'YES' : 'no');
                if (!empty($errorStats['error_counts'])) {
                    foreach ($errorStats['error_counts'] as $type => $count) {
                        $content[] = "  {$type}: {$count}";
                    }
                }
                
                $content[] = '';
                $content[] = 'Interactive Areas (Click to Test):';
                $content[] = '  [ CLICK BOX ]     [RESET STATS]    [ERROR TEST]';
                $content[] = '  Clicks: ' . $stats['clickBoxClicks'] . '         Clicks: ' . $stats['resetButtonClicks'] . '       Clicks: ' . $stats['errorTestClicks'];
                $content[] = '';
                $content[] = 'Testing Guide:';
                $content[] = '  Keys: Any key, Ctrl+keys, arrows, F-keys';
                $content[] = '  Mouse: Click areas above, scroll, modifier+click';
                $content[] = '  Error Test: Click "ERROR TEST" to trigger recoverable errors';
                $content[] = '  Right-click or \'q\' to quit (with guaranteed cleanup!)';
                
                $renderer->setContent($content);
            });
            
            $loop->onRender(function () use ($renderer, $terminal) {
                if ($renderer->isDirty()) {
                    $terminal->clear();
                    $terminal->write($renderer->render());
                    $renderer->clearDirty();
                }
            });
            
            // Start with bulletproof setup
            $terminal->enableRawMode();
            $terminal->enableMouseReporting();
            
            // Store globally for event handlers
            $GLOBALS['loop'] = $loop;
            
            $this->info('🛡️  Bulletproof TUI Demo - Comprehensive Error Handling');
            $this->info('Try clicking "ERROR TEST" to see error recovery in action!');
            $this->info('Press \'q\' or right-click to quit safely.');
            
            // Run with error handling
            $loop->start();
            
        } catch (\Exception $e) {
            // Final safety net
            if (isset($errorHandler)) {
                $errorHandler->emergencyCleanup();
            }
            
            $this->error('Demo failed: ' . $e->getMessage());
            return 1;
        } finally {
            // Guaranteed cleanup
            if (isset($terminal)) {
                try {
                    $terminal->disableMouseReporting();
                    $terminal->disableRawMode();
                } catch (\Exception $e) {
                    // Ignore cleanup errors
                }
            }
            unset($GLOBALS['loop'], $GLOBALS['shouldQuit']);
        }
        
        $this->info('🎉 Bulletproof demo completed successfully!');
        $this->info('Total events handled: ' . (($stats['keyPresses'] ?? 0) + ($stats['mousePresses'] ?? 0)));
        
        if (isset($errorStats) && $errorStats['total_errors'] > 0) {
            $this->info('🛡️  Errors handled gracefully: ' . $errorStats['total_errors']);
        }
        
        return 0;
    }

    private function isPointInArea(int $x, int $y, array $area): bool
    {
        return $x >= $area['x'] && 
               $x < $area['x'] + $area['width'] &&
               $y >= $area['y'] && 
               $y < $area['y'] + $area['height'];
    }
}