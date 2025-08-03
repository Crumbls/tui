<?php

declare(strict_types=1);

namespace Crumbls\Tui\Terminal;

use Crumbls\Tui\Terminal\Events\CharKeyEvent;
use Crumbls\Tui\Terminal\Events\CodedKeyEvent;

/**
 * Native event provider that parses terminal input and creates events
 */
class EventProvider
{
    private string $inputBuffer = '';

    public function __construct(
        private readonly NativeTerminal $terminal
    ) {
    }

    /**
     * Get the next event, or null if none available
     */
    public function next(): ?object
    {
        // First check if we have buffered input to process
        if ($this->inputBuffer !== '') {
            return $this->parseNextFromBuffer();
        }
        
        // Read new input
        $input = $this->terminal->readInput(0.01); // 10ms timeout
        
        if ($input === null || $input === '') {
            return null;
        }

        // Add to buffer and parse
        $this->inputBuffer .= $input;
        return $this->parseNextFromBuffer();
    }

    /**
     * Parse the next event from the input buffer
     */
    private function parseNextFromBuffer(): ?object
    {
        if ($this->inputBuffer === '') {
            return null;
        }

        // For now, just take one character at a time
        $char = $this->inputBuffer[0];
        $this->inputBuffer = substr($this->inputBuffer, 1);
        
        return $this->parseInput($char);
    }

    /**
     * Parse raw input into events
     */
    private function parseInput(string $input): ?object
    {
        // Handle escape sequences
        if ($input[0] === "\033") {
            return $this->parseEscapeSequence($input);
        }
        
        // Handle control characters
        $ord = ord($input[0]);
        
        // Tab
        if ($ord === 9) {
            return new CodedKeyEvent(KeyCode::Tab);
        }
        
        // Enter
        if ($ord === 13 || $ord === 10) {
            return new CodedKeyEvent(KeyCode::Enter);
        }
        
        // Backspace
        if ($ord === 127 || $ord === 8) {
            return new CodedKeyEvent(KeyCode::Backspace);
        }
        
        // Escape
        if ($ord === 27) {
            return new CodedKeyEvent(KeyCode::Esc);
        }
        
        // Control characters (Ctrl+A through Ctrl+Z)
        if ($ord >= 1 && $ord <= 26) {
            $char = chr($ord + 64); // Convert to A-Z
            return new CharKeyEvent(strtolower($char), KeyModifiers::CONTROL);
        }
        
        // Regular printable characters
        if ($ord >= 32 && $ord <= 126) {
            return new CharKeyEvent($input[0]);
        }
        
        // Unknown character - treat as char event
        return new CharKeyEvent($input[0]);
    }

    /**
     * Parse escape sequences (arrow keys, function keys, etc.)
     */
    private function parseEscapeSequence(string $input): ?object
    {
        if (strlen($input) < 2) {
            return new CodedKeyEvent(KeyCode::Esc);
        }
        
        // CSI sequences (ESC [)
        if ($input[1] === '[') {
            if (strlen($input) < 3) {
                return null;
            }
            
            $remaining = substr($input, 2);
            
            // Arrow keys
            if ($remaining === 'A') return new CodedKeyEvent(KeyCode::Up);
            if ($remaining === 'B') return new CodedKeyEvent(KeyCode::Down);
            if ($remaining === 'C') return new CodedKeyEvent(KeyCode::Right);
            if ($remaining === 'D') return new CodedKeyEvent(KeyCode::Left);
            
            // Home/End
            if ($remaining === 'H') return new CodedKeyEvent(KeyCode::Home);
            if ($remaining === 'F') return new CodedKeyEvent(KeyCode::End);
            
            // Page Up/Down
            if ($remaining === '5~') return new CodedKeyEvent(KeyCode::PageUp);
            if ($remaining === '6~') return new CodedKeyEvent(KeyCode::PageDown);
            
            // Delete
            if ($remaining === '3~') return new CodedKeyEvent(KeyCode::Delete);
            
            // Insert
            if ($remaining === '2~') return new CodedKeyEvent(KeyCode::Insert);
            
            // Back Tab (Shift+Tab)
            if ($remaining === 'Z') return new CodedKeyEvent(KeyCode::BackTab);
            
            // Function keys
            if (str_starts_with($remaining, '1') && str_ends_with($remaining, '~')) {
                $num = (int) substr($remaining, 1, -1);
                return match ($num) {
                    1 => new CodedKeyEvent(KeyCode::F1),
                    2 => new CodedKeyEvent(KeyCode::F2),
                    3 => new CodedKeyEvent(KeyCode::F3),
                    4 => new CodedKeyEvent(KeyCode::F4),
                    5 => new CodedKeyEvent(KeyCode::F5),
                    default => null,
                };
            }
        }
        
        // Alt + character
        if (strlen($input) === 2 && $input[1] >= 'a' && $input[1] <= 'z') {
            return new CharKeyEvent($input[1], KeyModifiers::ALT);
        }
        
        return new CodedKeyEvent(KeyCode::Esc);
    }
}