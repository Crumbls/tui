<?php

declare(strict_types=1);

namespace Crumbls\Tui\Console\Commands;

use Illuminate\Console\Command;
use Crumbls\Tui\MainLoop;
use Crumbls\Tui\Events\EventBus;
use Crumbls\Tui\Events\UserActivityEvent;
use Crumbls\Tui\Input\InputHandler;
use Crumbls\Tui\Events\KeyInputEvent;
use Crumbls\Tui\Events\MouseInputEvent;
use Crumbls\Tui\Terminal\Terminal;
use Crumbls\Tui\Rendering\SimpleRenderer;

class TuiDemoCommand extends Command
{
    protected $signature = 'tui:demo';
    protected $description = 'Demo the TUI main loop and idle detection';

    public function handle()
    {
        $this->info('Starting TUI Demo with integrated Phase 1 components...');
        
        // Create all our Phase 1 components
        $terminal = Terminal::make();
        $eventBus = new EventBus();
        $inputHandler = new InputHandler($terminal, $eventBus);
        $inputHandler->setMouseEnabled(true); // Enable mouse input
        $renderer = new SimpleRenderer($terminal);
        
        // Create integrated main loop
        $loop = MainLoop::create($terminal, $eventBus, $inputHandler)
            ->setTickRate(10) // 10 Hz when active
            ->setIdleTickRate(2) // 2 Hz when idle
            ->setIdleThreshold(3); // Idle after 3 seconds
        
        // Demo state
        $stats = [
            'tick' => 0,
            'keyPresses' => 0,
            'mousePresses' => 0,
            'lastKey' => 'none',
            'lastMouse' => 'none',
            'events' => [],
            'clickBoxClicks' => 0,
            'resetButtonClicks' => 0,
        ];

        // Define clickable areas
        // Note: Coordinates account for header line (y+1) and text positioning
        // "  [ CLICK BOX ]     [RESET STATS]" appears around line 29 (0-based: y=28)
        $clickBoxArea = ['x' => 3, 'y' => 14, 'width' => 24, 'height' => 1];     // [ CLICK BOX ]
        $resetButtonArea = ['x' => 21, 'y' => 28, 'width' => 12, 'height' => 1]; // [RESET STATS]
        
        // Listen for key events
        $eventBus->listen('KeyInputEvent', function (KeyInputEvent $event) use (&$stats, $eventBus) {
            $stats['keyPresses']++;
            $stats['lastKey'] = $event->getKey();
            $stats['lastRawInput'] = bin2hex($event->getRawInput());
            $stats['lastModifiers'] = $event->getModifiers();
            $stats['lastIsSpecial'] = $event->isSpecialKey();
            
            // Build detailed event description
            $eventDesc = $event->getKey();
            if (!empty($event->getModifiers())) {
                $eventDesc = implode('+', $event->getModifiers()) . '+' . $event->getKey();
            }
            if ($event->isSpecialKey()) {
                $eventDesc = '[' . $eventDesc . ']';
            }
            
            $stats['events'][] = $eventDesc . ' (raw: ' . bin2hex($event->getRawInput()) . ')';
            
            // Keep only last 8 events (more room for detailed info)
            if (count($stats['events']) > 8) {
                array_shift($stats['events']);
            }
            
            // Store quit request - will be handled in next tick
            if ($event->getKey() === 'q') {
                $GLOBALS['shouldQuit'] = true;
            }
            
            // Emit activity event
            $eventBus->emit(new UserActivityEvent());
        });

        // Listen for mouse events
        $eventBus->listen('MouseInputEvent', function (MouseInputEvent $event) use (&$stats, $eventBus, $clickBoxArea, $resetButtonArea) {
            $stats['mousePresses']++;
            
            // Build mouse description
            $mouseDesc = "{$event->getAction()} {$event->getButton()} at ({$event->getX()},{$event->getY()})";
            if (!empty($event->getModifiers())) {
                $mouseDesc = implode('+', $event->getModifiers()) . '+' . $mouseDesc;
            }
            $stats['lastMouse'] = $mouseDesc;
            
            $stats['events'][] = $mouseDesc . ' (raw: ' . bin2hex($event->getRawInput()) . ')';
            
            // Check if click is in clickable areas
            if ($event->getAction() === 'press' && $event->getButton() === 'left') {
                // Check click box
                if ($this->isPointInArea($event->getX(), $event->getY(), $clickBoxArea)) {
                    $stats['clickBoxClicks']++;
                    $stats['events'][] = '*** CLICK BOX HIT! ***';
                }
                
                // Check reset button
                if ($this->isPointInArea($event->getX(), $event->getY(), $resetButtonArea)) {
                    $stats['resetButtonClicks']++;
                    $stats['events'][] = '*** RESET BUTTON HIT! ***';
                    // Reset some stats
                    $stats['clickBoxClicks'] = 0;
                    $stats['keyPresses'] = 0;
                    $stats['mousePresses'] = 1; // Keep current mouse press
                }
            }
            
            // Keep only last 8 events
            if (count($stats['events']) > 8) {
                array_shift($stats['events']);
            }
            
            // Store quit request - will be handled in next tick
            if ($event->getAction() === 'press' && $event->getButton() === 'right') {
                $GLOBALS['shouldQuit'] = true;
            }
            
            // Emit activity event
            $eventBus->emit(new UserActivityEvent());
        });
        
        // Configure loop callbacks
        $loop->onTick(function () use (&$stats, $loop, $renderer, $terminal, $eventBus) {
            $stats['tick']++;
            
            // Check for quit request
            if ($GLOBALS['shouldQuit'] ?? false) {
                $loop->stop();
                return;
            }
            
            // Get terminal size
            $size = $terminal->getSize();
            $renderer->setSize($size->width, $size->height);
            
            // Get idle stats using reflection (like your original)
            $isIdle = (new \ReflectionProperty($loop, 'isIdle'))->getValue($loop);
            $lastActivity = (new \ReflectionProperty($loop, 'lastActivity'))->getValue($loop);
            $currentTime = microtime(true);
            $timeSinceActivity = $currentTime - $lastActivity;
            
            // Build content to display
            $content = [
                'Tick: ' . $stats['tick'],
                'Key Presses: ' . $stats['keyPresses'] . ' | Mouse Presses: ' . $stats['mousePresses'],
                'Last Key: ' . ($stats['lastKey'] ?? 'none'),
                'Last Mouse: ' . ($stats['lastMouse'] ?? 'none'),
                'Raw Input: ' . ($stats['lastRawInput'] ?? 'none'),
                'Modifiers: ' . (empty($stats['lastModifiers'] ?? []) ? 'none' : implode(', ', $stats['lastModifiers'])),
                'Special Key: ' . (($stats['lastIsSpecial'] ?? false) ? 'yes' : 'no'),
                'Idle: ' . ($isIdle ? 'yes' : 'no'),
                'Last Activity: ' . number_format($lastActivity, 4),
                'Time Since Activity: ' . number_format($timeSinceActivity, 2) . 's',
                '',
                'Recent Events (Key + Mouse):',
            ];
            
            foreach ($stats['events'] as $event) {
                $content[] = '  ' . $event;
            }
            
            $content[] = '';
            $content[] = 'Event Bus Stats:';
            $eventStats = $eventBus->getStats();
            $content[] = '  Total Events: ' . $eventStats['events_in_history'];
            $content[] = '  Event Types: ' . $eventStats['total_event_types'];
            $content[] = '';
            $content[] = 'Interactive Areas:';
            $content[] = '  [ CLICK BOX ]     [RESET STATS]';
            $content[] = '  Clicks: ' . $stats['clickBoxClicks'] . '         Clicks: ' . $stats['resetButtonClicks'];
            $content[] = '';
            $content[] = 'Testing Guide:';
            $content[] = '  Keys: Ctrl+C, Arrow keys, F1-F12, Alt+keys';
            $content[] = '  Mouse: Click boxes above, scroll wheel';
            $content[] = '  Right-click or \'q\' to quit';
            $content[] = '  Raw hex shows actual terminal sequences!';
            
            $renderer->setContent($content);
        });
        
        $loop->onRender(function () use ($renderer, $terminal) {
            if ($renderer->isDirty()) {
                $terminal->clear();
                $terminal->write($renderer->render());
                $renderer->clearDirty();
            }
        });
        
        // Store loop globally so event handler can access it
        $GLOBALS['loop'] = $loop;
        
        // Start the terminal
        $terminal->enableRawMode();
        $terminal->enableMouseReporting();
        
        try {
            $this->info('TUI Phase 1 Demo - Comprehensive Key & Mouse Testing');
            $this->info('Try modifier keys, special keys, mouse clicks, and scroll!');
            $this->info('Press \'q\' or right-click to quit.');
            $loop->start();
        } finally {
            $terminal->disableMouseReporting();
            $terminal->disableRawMode();
            unset($GLOBALS['loop'], $GLOBALS['shouldQuit']);
        }
        
        $this->info('Demo completed successfully!');
        $this->info('Key statistics: ' . ($stats['keyPresses'] ?? 0) . ' total key presses detected');
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
