<?php

namespace Crumbls\Tui\Terminal;

use Crumbls\Tui\Contracts\InputBusContract;
use Crumbls\Tui\Events\MouseEvent;

class InputBus implements InputBusContract
{
    protected bool $listening = false;
    protected bool $mouseTracking = true;
    protected array $keyHandlers = [];
    protected array $mouseHandlers = [];
    protected array $componentBounds = [];
    protected $stdin;

    public function __construct()
    {
        $this->stdin = fopen('php://stdin', 'r');
        
        // Enable raw mode for real-time input
        if (function_exists('system')) {
            system('stty -icanon -echo');
        }
        
        // Enable mouse tracking by default
        $this->hasMouseTracking(true);
    }

    public function __destruct()
    {
        $this->stopListening();
        
        // Disable mouse tracking
        $this->hasMouseTracking(false);
        
        // Restore normal terminal mode
        if (function_exists('system')) {
            system('stty icanon echo');
        }
        
        if (is_resource($this->stdin)) {
            fclose($this->stdin);
        }
    }

    public function startListening(): void
    {
        $this->listening = true;
    }

    public function stopListening(): void
    {
        $this->listening = false;
    }

    public function isListening(): bool
    {
        return $this->listening;
    }

    public function readKey(): ?string
    {
        if (!is_resource($this->stdin)) {
            return null;
        }

        $key = fgetc($this->stdin);
        
        if ($key === false) {
            return null;
        }

        // Handle escape sequences (special keys and mouse events)
        if (ord($key) === 27) { // ESC
            $next = fgetc($this->stdin);
            
            if ($next === '[') {
                // Read the full escape sequence
                $sequence = $key . $next;
                $char = fgetc($this->stdin);
                
                while ($char !== false && !ctype_alpha($char) && $char !== '~') {
                    $sequence .= $char;
                    $char = fgetc($this->stdin);
                }
                
                if ($char !== false) {
                    $sequence .= $char;
                }
                
                // Check if this is a mouse event
                if ($this->isMouseSequence($sequence)) {
                    $mouseEvent = $this->parseMouseEvent($sequence);
                    if ($mouseEvent) {
                        // Trigger mouse event handlers
                        foreach ($this->mouseHandlers as $handler) {
                            $handler($mouseEvent);
                        }
                    }
                    return null; // Don't return mouse events as key presses
                }
                
                return $sequence;
            } else {
                return $key . $next;
            }
        }

        return $key;
    }

    public function hasInput(): bool
    {
        if (!is_resource($this->stdin)) {
            return false;
        }

        $read = [$this->stdin];
        $write = null;
        $except = null;
        
        return stream_select($read, $write, $except, 0) > 0;
    }

    public function onKeyPress(callable $handler): void
    {
        $this->keyHandlers[] = $handler;
    }

    public function onMouseEvent(callable $handler): void
    {
        $this->mouseHandlers[] = $handler;
    }

    public function hasMouseTracking(bool $enabled = true): void
    {
        $this->mouseTracking = $enabled;
        
        if ($enabled) {
            // Enable mouse tracking with SGR extended coordinates
            echo "\e[?1000h\e[?1006h";
        } else {
            // Disable mouse tracking
            echo "\e[?1000l\e[?1006l";
        }
    }

    protected function isMouseSequence(string $sequence): bool
    {
        // SGR mouse format: \e[<button;x;y;M or \e[<button;x;y;m
        return preg_match('/\e\[<\d+;\d+;\d+[Mm]/', $sequence) === 1;
    }

    protected function parseMouseEvent(string $sequence): ?MouseEvent
    {
        // Parse SGR mouse format: \e[<button;x;y;M or \e[<button;x;y;m
        if (preg_match('/\e\[<(\d+);(\d+);(\d+)([Mm])/', $sequence, $matches)) {
            $buttonCode = (int) $matches[1];
            $x = (int) $matches[2];
            $y = (int) $matches[3];
            $action = $matches[4] === 'M' ? 'press' : 'release';

            // Parse button and modifiers
            $button = match ($buttonCode & 3) {
                0 => 'left',
                1 => 'middle', 
                2 => 'right',
                default => 'unknown'
            };

            $shift = ($buttonCode & 4) !== 0;
            $ctrl = ($buttonCode & 16) !== 0;
            $alt = ($buttonCode & 8) !== 0;

            // Handle drag events
            if ($buttonCode & 32) {
                $action = 'drag';
            }

            // Find clicked component using hit testing
            $clickedComponent = $this->getComponentAt($x, $y);

            return new MouseEvent($x, $y, $button, $action, $shift, $ctrl, $alt, $clickedComponent);
        }

        return null;
    }

    // =================== HIT TESTING ===================

    public function registerComponent($component, int $x, int $y, int $width, int $height, int $zIndex = 0): void
    {
        $componentId = is_object($component) ? $component->getId() : $component;
        $this->componentBounds[$componentId] = [
            'component' => $component,
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height,
            'x2' => $x + $width - 1,
            'y2' => $y + $height - 1,
            'zIndex' => $zIndex
        ];
    }

    public function getComponentAt(int $x, int $y)
    {
        // Find all components that contain this point
        $candidates = [];
        
        foreach ($this->componentBounds as $bound) {
            if ($x >= $bound['x'] && $x <= $bound['x2'] && 
                $y >= $bound['y'] && $y <= $bound['y2']) {
                $candidates[] = $bound;
            }
        }

        // If no candidates, return null
        if (empty($candidates)) {
            return null;
        }
        dd(array_map(function($e) {
			return [
				'title' => $e->getTitle(),
				'depth' => $e->getDepth(),
				'id' => $e->getId()
				];
			},
	        array_column($candidates, 'component')));
        // Debug: Show all candidates
        echo "\nClick at ({$x}, {$y}) - Candidates:\n";
        foreach ($candidates as $candidate) {
            echo "- {$candidate['component']->getId()}: depth={$candidate['zIndex']}, bounds=({$candidate['x']},{$candidate['y']},{$candidate['x2']},{$candidate['y2']})\n";
        }
        
        // Sort by z-index (highest first)
        usort($candidates, function($a, $b) {
            return $b['zIndex'] <=> $a['zIndex'];
        });
        
        echo "Winner: {$candidates[0]['component']->getId()} (depth={$candidates[0]['zIndex']})\n\n";
        
        // Return component with highest z-index
        return $candidates[0]['component'];
    }

    public function clearComponents(): void
    {
        $this->componentBounds = [];
    }
}