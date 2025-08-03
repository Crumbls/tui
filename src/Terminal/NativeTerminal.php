<?php

declare(strict_types=1);

namespace Crumbls\Tui\Terminal;

use RuntimeException;

/**
 * Native Terminal implementation that directly handles terminal operations
 * without depending on PhpTui's Terminal.
 */
class NativeTerminal
{
    private bool $rawModeEnabled = false;
    private array $originalSettings = [];
    
    /** @var resource */
    private $stdin;
    
    /** @var resource */
    private $stdout;
    
    private ?EventProvider $eventProvider = null;

    public function __construct()
    {
        $this->stdin = STDIN;
        $this->stdout = STDOUT;
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * Show or hide the cursor
     */
    public function showCursor(bool $show = true): self
    {
        $this->write($show ? "\033[?25h" : "\033[?25l");
        return $this;
    }

    /**
     * Hide the cursor (convenience method)
     */
    public function hideCursor(): self
    {
        return $this->showCursor(false);
    }

    /**
     * Enable or disable alternate screen
     */
    public function alternateScreen(bool $enable = true): self
    {
        $this->write($enable ? "\033[?1049h" : "\033[?1049l");
        return $this;
    }

    /**
     * Clear the terminal screen
     */
    public function clear(): self
    {
        $this->write("\033[2J\033[H");
        return $this;
    }

    /**
     * Enable or disable mouse capture
     */
    public function mouseCapture(bool $enable = true): self
    {
        if ($enable) {
            $this->write("\033[?1000h\033[?1002h\033[?1003h\033[?1015h\033[?1006h");
        } else {
            $this->write("\033[?1006l\033[?1015l\033[?1003l\033[?1002l\033[?1000l");
        }
        return $this;
    }

    /**
     * Move cursor to specific position
     */
    public function moveCursor(int $x, int $y): self
    {
        $this->write("\033[{$y};{$x}H");
        return $this;
    }

    /**
     * Move cursor by relative amount
     */
    public function moveCursorBy(int $dx = 0, int $dy = 0): self
    {
        if ($dx > 0) {
            $this->write("\033[{$dx}C");
        } elseif ($dx < 0) {
            $this->write("\033[" . abs($dx) . "D");
        }
        
        if ($dy > 0) {
            $this->write("\033[{$dy}B");
        } elseif ($dy < 0) {
            $this->write("\033[" . abs($dy) . "A");
        }
        
        return $this;
    }

    /**
     * Set terminal title
     */
    public function title(string $title): self
    {
        $this->write("\033]0;{$title}\007");
        return $this;
    }

    /**
     * Write text to terminal
     */
    public function write(string $text): self
    {
        fwrite($this->stdout, $text);
        return $this;
    }

    /**
     * Print text to terminal
     */
    public function print(string $text): self
    {
        return $this->write($text);
    }

    /**
     * Enable raw mode using stty
     */
    public function rawMode(bool $enable = true): self
    {
        if ($enable && !$this->rawModeEnabled) {
            $this->enableRawMode();
        } elseif (!$enable && $this->rawModeEnabled) {
            $this->disableRawMode();
        }
        return $this;
    }

    /**
     * Get terminal size
     */
    public function size(): ?array
    {
        // Try to get size from environment variables first
        $columns = getenv('COLUMNS');
        $lines = getenv('LINES');
        
        if ($columns !== false && $lines !== false) {
            return ['width' => (int) $columns, 'height' => (int) $lines];
        }
        
        // Try to get size using stty
        $output = shell_exec('stty size 2>/dev/null');
        if ($output && preg_match('/(\d+) (\d+)/', trim($output), $matches)) {
            return ['width' => (int) $matches[2], 'height' => (int) $matches[1]];
        }
        
        // Try tput as fallback
        $cols = shell_exec('tput cols 2>/dev/null');
        $rows = shell_exec('tput lines 2>/dev/null');
        if ($cols && $rows) {
            return ['width' => (int) trim($cols), 'height' => (int) trim($rows)];
        }
        
        // Default fallback
        return ['width' => 80, 'height' => 24];
    }

    /**
     * Read a single character from stdin (non-blocking)
     */
    public function readChar(): ?string
    {
        if (!$this->rawModeEnabled) {
            return null;
        }
        
        // Set non-blocking mode
        stream_set_blocking($this->stdin, false);
        $char = fread($this->stdin, 1);
        stream_set_blocking($this->stdin, true);
        
        return $char === false || $char === '' ? null : $char;
    }

    /**
     * Read input with timeout
     */
    public function readInput(float $timeout = 0.1): ?string
    {
        if (!$this->rawModeEnabled) {
            // If not in raw mode, try to read anyway for testing
            stream_set_blocking($this->stdin, false);
            $input = fread($this->stdin, 1024);
            stream_set_blocking($this->stdin, true);
            return $input === false || $input === '' ? null : $input;
        }

        $read = [$this->stdin];
        $write = [];
        $except = [];
        
        $timeoutSec = (int) $timeout;
        $timeoutMicro = (int) (($timeout - $timeoutSec) * 1000000);
        
        $result = stream_select($read, $write, $except, $timeoutSec, $timeoutMicro);
        
        if ($result > 0) {
            $input = fread($this->stdin, 1024);
            return $input === false || $input === '' ? null : $input;
        }
        
        return null;
    }

    /**
     * Set up terminal for TUI application (common setup)
     */
    public function setupForTui(): self
    {
        $this->hideCursor()
            ->alternateScreen(true)
            ->mouseCapture(true)
            ->flush();
            
        // Try to enable raw mode, but don't fail if it doesn't work
        try {
            $this->rawMode(true);
        } catch (\Throwable $e) {
            // Raw mode failed - continue without it
        }
        
        return $this;
    }

    /**
     * Flush output
     */
    public function flush(): self
    {
        fflush($this->stdout);
        return $this;
    }

    /**
     * Get the event provider for reading terminal events
     */
    public function events(): EventProvider
    {
        if ($this->eventProvider === null) {
            $this->eventProvider = new EventProvider($this);
        }
        return $this->eventProvider;
    }

    /**
     * Enable raw mode using stty
     */
    private function enableRawMode(): void
    {
        // Store original settings
        $output = shell_exec('stty -g 2>/dev/null');
        if ($output) {
            $this->originalSettings = ['stty' => trim($output)];
        } else {
            throw new RuntimeException('Could not get current stty settings');
        }
        
        // Enable raw mode - need to capture both stdout and stderr
        $command = 'stty raw -echo 2>&1';
        $output = shell_exec($command);
        
        // Check if stty command succeeded (no output usually means success)
        if ($output !== null && $output !== '') {
            throw new RuntimeException("Failed to enable raw mode: " . trim($output));
        }
        
        $this->rawModeEnabled = true;
    }

    /**
     * Disable raw mode and restore original settings
     */
    private function disableRawMode(): void
    {
        if (isset($this->originalSettings['stty'])) {
            shell_exec("stty {$this->originalSettings['stty']} 2>/dev/null");
        } else {
            shell_exec('stty cooked echo 2>/dev/null');
        }
        
        $this->rawModeEnabled = false;
    }

    /**
     * Automatic cleanup when the terminal object is destroyed
     */
    public function __destruct()
    {
        try {
            // Force disable mouse capture first
            $this->write("\033[?1006l\033[?1015l\033[?1003l\033[?1002l\033[?1000l");
            
            $this->rawMode(false)
                ->alternateScreen(false)
                ->showCursor()
                ->flush();
        } catch (\Throwable $e) {
            // Ignore cleanup errors during destruction
        }
    }
}