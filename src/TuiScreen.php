<?php

declare(strict_types=1);

namespace Crumbls\Tui;

use Crumbls\Tui\Contracts\ThemeInterface;
use Crumbls\Tui\Layout\Layout;
use Crumbls\Tui\Style\ThemeManager;

/**
 * TuiScreen manages the redraw/input loop for a TUI session.
 * - Handles alternate screen buffer, raw mode, and terminal resizing.
 * - Accepts a root Layout or Widget tree.
 * - Runs the redraw loop and input polling, passing terminal size to the layout on every frame.
 * - End users only declare their layout/widget tree and wait for input/result.
 */
class TuiScreen
{
    protected $root;
    protected $running = true;

    public function __construct($root = null)
    {
        $this->root = $root;
        
        // Initialize default themes
        if (!ThemeManager::hasTheme('default')) {
            ThemeManager::registerDefaultThemes();
        }
        
        // Set terminal to raw mode, disable echo, and disable XON/XOFF (Ctrl+S/Ctrl+Q)
        system('stty cbreak -echo -ixon');
    }

    /**
     * Start the redraw/input loop. Blocks until exit condition is met.
     */
    public function run(): void
    {
        // Enter alternate screen buffer and raw mode
        echo "\033[?1049h";
        echo "\033[3J";

        try {
            while ($this->running) {
                // Query terminal size every frame
                $rows = (int) exec('tput lines');
                $cols = (int) exec('tput cols');

                // Set region on root layout/widget
                if (method_exists($this->root, 'setRegion')) {
                    $this->root->setRegion($cols, $rows);
                }

                // Render the full frame from the root layout/widget
                $frame = $this->root->render();

                // Write to screen (move cursor to top, clear thoroughly, print)
                echo "\033[H\033[2J\033[3J";
                echo $frame;
                
                // Ensure cursor is positioned properly after render
                echo "\033[H";

                // Poll for input (example: quit on 'q')
                stream_set_blocking(STDIN, false);
                $char = $this->readKey();
                if ($char !== null && strtolower($char) === 'q') {
                    $this->running = false;
                }
                usleep(100_000);
            }


        } finally {
            // Restore terminal state
            echo "\033[?1049l";
            $this->cleanup();
        }
    }

    /**
     * Set the root layout/widget for the next draw.
     */
    public function setRoot($root): void
    {
        $this->root = $root;
    }

    /**
     * Draw the current root layout/widget once.
     */
    public function draw(): void
    {
        $rows = (int) exec('tput lines');
        $cols = (int) exec('tput cols');
        if (method_exists($this->root, 'setRegion')) {
            $this->root->setRegion($cols, $rows);
        }
        $frame = $this->root->render();
        
        // Clear screen more thoroughly - move cursor to home, clear entire screen and scrollback buffer
        echo "\033[H\033[2J\033[3J";
        echo $frame;
        
        // Ensure cursor is positioned properly after render
        echo "\033[H";
    }

    /**
     * Read a single keypress from STDIN, optionally with timeout (seconds).
     */
    public function readKey(float $timeout = 0): ?string
    {
        $read = [STDIN];
        $write = $except = [];
        $sec = (int) $timeout;
        $usec = (int) (($timeout - $sec) * 1_000_000);
        if (stream_select($read, $write, $except, $sec, $usec) > 0) {
            $char = fgetc(STDIN);
            if ($char === false) {
                return null;
            }
            // Handle escape sequences for arrows
            if ($char === "\033") {
                $seq = $char;
                for ($i = 0; $i < 2; $i++) {
                    $next = fgetc(STDIN);
                    if ($next === false) break;
                    $seq .= $next;
                }
                return $seq;
            }
            return $char;
        }
        return null;
    }

    /**
     * Set the theme using a theme instance.
     */
    public function theme(ThemeInterface $theme): static
    {
        ThemeManager::setTheme($theme);
        return $this;
    }

    /**
     * Set the theme using a theme name.
     */
    public function useTheme(string $themeName): static
    {
        ThemeManager::setTheme($themeName);
        return $this;
    }

    /**
     * Register a custom theme.
     */
    public function registerTheme(ThemeInterface $theme): static
    {
        ThemeManager::register($theme);
        return $this;
    }

    /**
     * Restore terminal state (alternate buffer, raw mode).
     */
    public function cleanup(): void
    {
        // Restore terminal state (cbreak, echo, XON/XOFF)
        system('stty -cbreak echo ixon');
    }
}
