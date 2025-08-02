<?php

declare(strict_types=1);

namespace Crumbls\Tui\Terminal;

use Crumbls\Tui\Contracts\TerminalInterface;

/**
 * Real terminal implementation using system calls.
 */
class Terminal implements TerminalInterface
{
    private bool $rawModeEnabled = false;
    private ?string $originalTerminalState = null;
    private array $commandQueue = [];
    private ?array $lastCursorPosition = null;

    /**
     * Create a new terminal instance (Laravel style).
     */
    public static function make(): static
    {
        return new static();
    }

    /**
     * Create terminal and immediately enable raw mode.
     */
    public static function raw(): static
    {
        $terminal = new static();
        $terminal->enableRawMode();
        return $terminal;
    }

    public function getSize(): Size
    {
        $width = (int) exec('tput cols') ?: 80;
        $height = (int) exec('tput lines') ?: 24;
        
        return new Size($width, $height);
    }

    public function readKey(float $timeout = 0): ?string
    {
//	    dd(__LINE__);

	    $read = [STDIN];
        $write = $except = [];
        $sec = (int) $timeout;
        $usec = (int) (($timeout - $sec) * 1_000_000);

        if (stream_select($read, $write, $except, $sec, $usec) > 0) {
            $char = fgetc(STDIN);
            if ($char === false) {
                return null;
            }

            // Handle escape sequences (arrows, function keys, mouse, etc.)
            if ($char === "\033") {
                $seq = $char;
                
                // Set non-blocking mode for reading additional characters
                stream_set_blocking(STDIN, false);
                
                // Read up to 15 characters to handle longer sequences (mouse can be long)
                for ($i = 0; $i < 15; $i++) {
                    $next = fgetc(STDIN);
                    if ($next === false) break;
                    $seq .= $next;

                    // Stop reading after terminating characters
                    if (in_array($next, ['A', 'B', 'C', 'D', 'Z', '~', 'M', 'm'])) {
                        break;
                    }
                    
                    // Special handling for sequences that might not have clear terminators
                    // If we see a pattern that looks complete, stop reading
                    if (strlen($seq) >= 3) {
                        // Function key patterns like \033OP, \033OQ  
                        if ($seq[1] === 'O' && ctype_alpha($seq[2])) {
                            break;
                        }
                        // Some other short sequences
                        if (preg_match('/^\033\[[0-9;]*[a-zA-Z]$/', $seq)) {
                            break;
                        }
                    }
                }
                
                // CRITICAL: Always restore blocking mode
                stream_set_blocking(STDIN, true);
                return $seq;
            }

            return $char;
        }

        return null;
    }

    public function write(string $content): void
    {
        echo $content;
    }

    public function queue(string $command): void
    {
        $this->commandQueue[] = $command;
    }

    public function flush(): void
    {
        if (empty($this->commandQueue)) {
            return;
        }
        
        $output = implode('', $this->commandQueue);
        echo $output;
        $this->commandQueue = [];
    }

    public function moveCursor(int $x, int $y): void
    {
        // Only move cursor if position actually changed (optimization from PhpTui)
        if ($this->lastCursorPosition === [$x, $y]) {
            return;
        }
        
        $this->queue("\033[" . ($y + 1) . ';' . ($x + 1) . 'H');
        $this->lastCursorPosition = [$x, $y];
    }

    public function getCursorPosition(): ?array
    {
        if (!$this->rawModeEnabled) {
            $this->enableRawMode();
        }
        
        // Request cursor position
        $this->write("\033[6n");
        
        $response = '';
        $startTime = microtime(true);
        
        // Read response with timeout
        while (microtime(true) - $startTime < 2.0) {
            $char = $this->readKey(0.1);
            if ($char === null) {
                continue;
            }
            
            $response .= $char;
            
            // Check if we have complete response: \033[row;colR
            if (preg_match('/\033\[(\d+);(\d+)R/', $response, $matches)) {
                return [(int)$matches[2] - 1, (int)$matches[1] - 1]; // [x, y]
            }
        }
        
        return null;
    }

    public function setForegroundColor(int $r, int $g, int $b): void
    {
        $this->queue("\033[38;2;{$r};{$g};{$b}m");
    }

    public function setBackgroundColor(int $r, int $g, int $b): void
    {
        $this->queue("\033[48;2;{$r};{$g};{$b}m");
    }

    public function resetColors(): void
    {
        $this->queue("\033[0m");
    }

    public function clear(): void
    {
        // Clear screen and move cursor to home
        $this->write("\033[H\033[2J");
    }

    public function enableRawMode(): void
    {
        if ($this->rawModeEnabled) {
            return;
        }

        // Save current terminal state
        $this->originalTerminalState = exec('stty -g');
        
        // Enable raw mode: no echo, process keys immediately
        system('stty cbreak -echo');
        $this->rawModeEnabled = true;
    }

    public function disableRawMode(): void
    {
        if (!$this->rawModeEnabled) {
            return;
        }

        // Restore original terminal state
        if ($this->originalTerminalState) {
            system('stty ' . $this->originalTerminalState);
        } else {
            system('stty -cbreak echo'); // Fallback
        }
        
        $this->rawModeEnabled = false;
        $this->originalTerminalState = null;
    }

    public function supportsColors(): bool
    {
        $term = $_ENV['TERM'] ?? '';
        
        // Known non-color terminals
        $noColorTerms = ['dumb', 'unknown', ''];
        if (in_array($term, $noColorTerms)) {
            return false;
        }
        
        // Check for explicit color support indicators
        if (strpos($term, 'color') !== false || strpos($term, '256') !== false) {
            return true;
        }
        
        // Check COLORTERM environment variable (many modern terminals set this)
        if (!empty($_ENV['COLORTERM'])) {
            return true;
        }
        
        // Most modern terminals support colors, assume yes unless proven otherwise
        return true;
    }

    public function supportsMouse(): bool
    {
        $term = $_ENV['TERM'] ?? '';
        
        // Skip mouse support for known non-interactive terminals
        $nonInteractiveTerms = ['dumb', 'unknown', ''];
        if (in_array($term, $nonInteractiveTerms)) {
            return false;
        }
        
        // Skip if we're clearly in a non-terminal environment
        // Note: posix_isatty can be unreliable in some PHP environments,
        // so we're being less strict here to avoid false negatives
        if (function_exists('posix_isatty')) {
            // Only block if BOTH stdin and stdout are clearly not TTY
            // This allows for edge cases where PHP doesn't detect TTY correctly
            if (!posix_isatty(STDIN) && !posix_isatty(STDOUT)) {
                // Additional check: if we're in a web context, definitely no mouse
                if (isset($_SERVER['REQUEST_METHOD']) || php_sapi_name() === 'apache2handler') {
                    return false;
                }
                // Otherwise, let it through and let mouse reporting fail gracefully
            }
        }
        
        // For all other terminals, assume mouse support and let it fail gracefully
        // Modern terminals (xterm, iTerm2, Alacritty, Kitty, Wezterm, etc.) all support it
        return true;
    }

    public function enableMouseReporting(): void
    {
        if (!$this->supportsMouse()) {
            return;
        }

        // Use the simplest mouse reporting that we know works
        // Based on testing, the user's terminal works with basic mode
        $this->write("\033[?1000h"); // Enable basic mouse tracking (click/release only)
    }

    public function disableMouseReporting(): void
    {
        if (!$this->supportsMouse()) {
            return;
        }

        // Disable the basic mouse tracking we enabled
        $this->write("\033[?1000l");
        
        // Also send cleanup for any other modes that might have been enabled
        $this->write(
            "\033[?1015l" .  // Disable urxvt mouse mode (cleanup)
            "\033[?1006l" .  // Disable SGR mouse mode (cleanup)
            "\033[?1003l" .  // Disable all motion tracking (cleanup)
            "\033[?1002l"    // Disable motion tracking (cleanup)
        );
    }

    public function __destruct()
    {
        // Ensure we clean up mouse reporting and raw mode on destruction
        $this->disableMouseReporting();
        $this->disableRawMode();
    }
}