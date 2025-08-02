<?php

declare(strict_types=1);

namespace Crumbls\Tui\Input;

use Crumbls\Tui\Events\KeyInputEvent;
use Crumbls\Tui\Events\MouseInputEvent;

use Crumbls\Tui\Contracts\InputHandlerInterface;
use Crumbls\Tui\Contracts\InputEventInterface;
use Crumbls\Tui\Contracts\TerminalInterface;
use Crumbls\Tui\Contracts\EventBusInterface;

/**
 * Handles parsing terminal input and emitting input events.
 */
class InputHandler implements InputHandlerInterface
{
    private bool $mouseEnabled = false;

    /**
     * Input buffer for accumulating multi-byte sequences.
     */
    private string $inputBuffer = '';

    public function __construct(
        private TerminalInterface $terminal,
        private EventBusInterface $eventBus
    ) {
    }

    /**
     * Process available input from terminal, buffer, and parse complete events.
     * Returns true if any event was emitted.
     */
    public function processInput(float $timeout = 0): bool
    {
        $emitted = false;
        
        // Handle input differently for FakeTerminal vs real Terminal
        if ($this->terminal instanceof \Crumbls\Tui\Testing\FakeTerminal) {
            // For FakeTerminal, use readKey() which pulls from queue
            $input = $this->terminal->readKey($timeout);
            if ($input !== null) {
                $this->inputBuffer .= $input;
            }
        } else {
            // For real Terminal, read from STDIN to get complete sequences
            $read = [STDIN];
            $write = null;
            $except = null;
            $timeoutSec = (int)$timeout;
            $timeoutUsec = (int)(($timeout - $timeoutSec) * 1000000);
            
            if (stream_select($read, $write, $except, $timeoutSec, $timeoutUsec) > 0) {
                $input = fread(STDIN, 4096);
                if ($input !== false && $input !== '') {
                    $this->inputBuffer .= $input;
                }
            }
        }
        
        // Process buffered input
        while ($this->inputBuffer !== '') {
            $event = null;
            if ($this->mouseEnabled && $this->isMouseSequence($this->inputBuffer)) {
                $event = $this->parseMouseEvent($this->inputBuffer);
                if ($event) {
                    $length = strlen($event->getRawInput());
                    $this->inputBuffer = substr($this->inputBuffer, $length);
                } else {
                    break;
                }
            } else {
                $event = $this->parseKeySequence($this->inputBuffer);
                if ($event) {
                    $length = strlen($event->getRawInput());
                    $this->inputBuffer = substr($this->inputBuffer, $length);
                } else {
                    break;
                }
            }
            if ($event && $event->shouldHandle()) {
                $this->eventBus->emit($event);
                $emitted = true;
            }
        }
        return $emitted;
    }

    public function parseKeySequence(string $sequence): ?InputEventInterface
    {
        // Handle escape sequences (arrows, function keys, etc.)
        if (str_starts_with($sequence, "\033[")) {
            return $this->parseEscapeSequence($sequence);
        }

        // Handle single characters and special keys
        return $this->parseSingleKey($sequence);
    }

    public function parseMouseEvent(string $sequence): ?InputEventInterface
    {
        if (!$this->isMouseSequence($sequence)) {
            return null;
        }

		// Parse X10 mouse protocol (only if exactly 6 bytes)
		if (strlen($sequence) === 6 && str_starts_with($sequence, "\033[M")) {
			return $this->parseX10MouseEvent($sequence);
		}

        // Parse SGR format: \033[<button;x;y[Mm]
        if (str_starts_with($sequence, "\033[<")) {
            return $this->parseSgrMouseEvent($sequence);
        }

        // Parse old format: \033[M + 3 bytes
        if (str_starts_with($sequence, "\033[M")) {
            return $this->parseOldMouseEvent($sequence);
        }

        // Parse numeric format: \033[num;num;num[Mm]
        if (str_starts_with($sequence, "\033[") && preg_match('/^\033\[(\d+);(\d+);(\d+)([Mm])$/', $sequence)) {
            return $this->parseNumericMouseEvent($sequence);
        }

        return null;
    }

    public function setMouseEnabled(bool $enabled): void
    {
        $this->mouseEnabled = $enabled;
    }

    public function isMouseEnabled(): bool
    {
        return $this->mouseEnabled;
    }

    /**
     * Parse escape sequences like arrow keys, function keys, etc.
     */
    private function parseEscapeSequence(string $sequence): ?InputEventInterface
    {
        $key = match ($sequence) {
            "\033[A" => 'ArrowUp',
            "\033[B" => 'ArrowDown',
            "\033[C" => 'ArrowRight',
            "\033[D" => 'ArrowLeft',
            "\033[Z" => 'ShiftTab',
            "\033[H" => 'Home',
            "\033[F" => 'End',
            "\033[3~" => 'Delete',
            "\033[5~" => 'PageUp',
            "\033[6~" => 'PageDown',
            default => null,
        };

        if ($key === null) {
            // Unknown escape sequence - return as raw
            return new KeyInputEvent($sequence, $sequence, true);
        }

        $modifiers = [];
        if ($key === 'ShiftTab') {
            $modifiers[] = 'shift';
        }

        return new KeyInputEvent($key, $sequence, true, $modifiers);
    }

    /**
     * Parse single character keys and control characters.
     */
    private function parseSingleKey(string $sequence): ?InputEventInterface
    {
        if (strlen($sequence) !== 1) {
            // Multi-character sequence that's not an escape - treat as raw
            return new KeyInputEvent($sequence, $sequence, true);
        }

        $char = $sequence[0];
        $ord = ord($char);

        // Handle control characters
        if ($ord < 32) {
            return $this->parseControlCharacter($char, $ord);
        }

        // Handle special characters
        if ($ord === 127) {
            return new KeyInputEvent('Backspace', $sequence, true);
        }

        // Regular printable character
        return new KeyInputEvent($char, $sequence, false);
    }

    /**
     * Parse control characters (Ctrl+key combinations).
     */
    private function parseControlCharacter(string $char, int $ord): KeyInputEvent
    {
        $key = match ($ord) {
            9 => 'Tab',
            10, 13 => 'Enter',
            27 => 'Escape',
            default => 'Ctrl+' . chr(ord('A') + $ord - 1),
        };

        $modifiers = [];
        if (str_starts_with($key, 'Ctrl+')) {
            $modifiers[] = 'ctrl';
        }

        return new KeyInputEvent($key, $char, true, $modifiers);
    }

	private function parseX10MouseEvent(string $sequence): ?MouseInputEvent {
        $raw = $sequence; // e.g., "\033[M {&"
        $btnByte = ord($raw[3]);
        $buttonCode = $btnByte - 32;
        $button = $buttonCode & 0x03; // 0: left, 1: middle, 2: right, 3: release

        switch ($button) {
            case 0: $btnName = 'left'; break;
            case 1: $btnName = 'middle'; break;
            case 2: $btnName = 'right'; break;
            case 3: $btnName = 'release'; break;
            default: $btnName = 'unknown';
        }

        // X and Y are also encoded as ASCII + 32
        $x = ord($raw[4]) - 32;
        $y = ord($raw[5]) - 32;

        // Optionally, extract modifiers from buttonCode (bits 2-7)
        $modifiers = ($buttonCode & ~0x03);

        // Convert modifiers int to array for MouseInputEvent
        $modifierArr = [];
        if ($modifiers & 4) $modifierArr[] = 'shift';
        if ($modifiers & 8) $modifierArr[] = 'meta';
        if ($modifiers & 16) $modifierArr[] = 'ctrl';

        // Return a MouseInputEvent (assuming constructor: action, x, y, button, raw, modifiers)
        return new MouseInputEvent(
            'click', // action/button name  
            $x,
            $y,
            $btnName, // pass string for button
            substr($raw, 0, 6), // just the 6-byte sequence
            $modifierArr
        );
    }

    /**
     * Parse SGR format mouse event: \033[<button;x;y[Mm]
     */
    private function parseSgrMouseEvent(string $sequence): ?MouseInputEvent
    {
        // Pattern: \033[<button;x;y[Mm]
        if (!preg_match('/^\033\[<(\d+);(\d+);(\d+)([Mm])$/', $sequence, $matches)) {
            return null;
        }

        $buttonCode = (int) $matches[1];
        $x = (int) $matches[2];
        $y = (int) $matches[3];
        $terminator = $matches[4];

        $action = $terminator === 'M' ? 'press' : 'release';
        
        // Parse button and modifiers
        $button = $this->parseButtonCode($buttonCode);
        $modifiers = $this->parseModifiers($buttonCode);

        // Handle special cases
        if ($buttonCode >= 64 && $buttonCode <= 67) {
            $action = 'scroll';
            $button = ($buttonCode === 64) ? 'up' : 'down';
        }

        return new MouseInputEvent($action, $x, $y, $button, $sequence, $modifiers);
    }

    /**
     * Parse old format mouse event: \033[M + 3 bytes
     */
    private function parseOldMouseEvent(string $sequence): ?MouseInputEvent
    {
        // Old format is \033[M followed by exactly 3 bytes
        if (strlen($sequence) !== 6 || !str_starts_with($sequence, "\033[M")) {
            return null;
        }

        $buttonByte = ord($sequence[3]);
        $x = ord($sequence[4]) - 32;
        $y = ord($sequence[5]) - 32;

        $button = $this->parseButtonCode($buttonByte & 0x03);
        $modifiers = $this->parseModifiers($buttonByte);
        $action = 'click'; // Old format doesn't distinguish press/release

        return new MouseInputEvent($action, $x, $y, $button, $sequence, $modifiers);
    }

    /**
     * Parse numeric format mouse event: \033[num;num;num[Mm]
     */
    private function parseNumericMouseEvent(string $sequence): ?MouseInputEvent
    {
        // Pattern: \033[button;x;y[Mm]
        if (!preg_match('/^\033\[(\d+);(\d+);(\d+)([Mm])$/', $sequence, $matches)) {
            return null;
        }

        $buttonCode = (int) $matches[1];
        $x = (int) $matches[2];
        $y = (int) $matches[3];
        $terminator = $matches[4];

        $action = $terminator === 'M' ? 'press' : 'release';
        
        // Parse button and modifiers (similar to SGR format)
        $button = $this->parseButtonCode($buttonCode);
        $modifiers = $this->parseModifiers($buttonCode);

        // Handle special cases like scroll
        if ($buttonCode >= 64 && $buttonCode <= 67) {
            $action = 'scroll';
            $button = ($buttonCode === 64) ? 'up' : 'down';
        }

        return new MouseInputEvent($action, $x, $y, $button, $sequence, $modifiers);
    }

    /**
     * Parse button code to button name.
     */
    private function parseButtonCode(int $code): string
    {
        // Handle different terminal button encoding schemes
        
        // Some terminals use 32+ encoding but with different button mapping
        if ($code >= 32) {
            // For codes 32-67, map directly based on observed patterns
            return match($code) {
                32 => 'left',         // 32=left press in standard mode
                33 => 'middle',       // 33=middle press
                34 => 'right',        // 34=right press  
                35 => 'left',         // 35=left press in some terminals
                36 => 'middle',       // 36=middle press variant
                37 => 'right',        // 37=right press variant
                64 => 'up',           // Scroll up
                65 => 'down',         // Scroll down
                // Mouse movement/drag events - these should be filtered out
                67, 99, 100, 101, 102, 103 => 'none', // Common drag/movement codes
                default => match(($code - 32) & 0x03) {
                    0 => 'left',
                    1 => 'middle', 
                    2 => 'right',
                    3 => 'none', // Drag or release
                    default => 'none'
                }
            };
        }
        
        // Standard 0-based encoding
        return match($code & 0x03) {
            0 => 'left',
            1 => 'middle',
            2 => 'right',
            3 => 'none', // Usually drag or release
            default => 'left'
        };
    }

    /**
     * Parse modifier keys from button code.
     */
    private function parseModifiers(int $code): array
    {
        $modifiers = [];
        
        if ($code & 0x04) $modifiers[] = 'shift';
        if ($code & 0x08) $modifiers[] = 'alt';
        if ($code & 0x10) $modifiers[] = 'ctrl';
        
        return $modifiers;
    }

    /**
     * Check if a sequence looks like a mouse event.
     */
    private function isMouseSequence(string $sequence): bool
    {
		if (preg_match('/^\033\[M.{3}/', $sequence)) {
			return true;
		}

        if (str_starts_with($sequence, "\033[<")) {
            return true; // SGR mouse format
        }
        
        // Check for numeric format: \033[num;num;num[Mm]
        if (preg_match('/^\033\[(\d+);(\d+);(\d+)([Mm])$/', $sequence)) {
            return true; // Numeric mouse format
        }
        
        return false;
    }
}