<?php

declare(strict_types=1);

namespace Crumbls\Tui\ErrorHandling;

use Crumbls\Tui\Contracts\ErrorHandlerInterface;
use Crumbls\Tui\Contracts\TerminalInterface;
use Crumbls\Tui\Exceptions\TuiException;
use Crumbls\Tui\Exceptions\TerminalException;
use Crumbls\Tui\Exceptions\RenderException;
use Exception;

/**
 * Production error handler that ensures terminal cleanup and graceful degradation.
 */
class ErrorHandler implements ErrorHandlerInterface
{
    private array $errorHistory = [];
    private array $errorCounts = [];
    private array $cleanupHandlers = [];
    private bool $emergencyMode = false;

    public function __construct(
        private ?TerminalInterface $terminal = null,
        private bool $logErrors = true
    ) {
        // Register PHP shutdown handler for emergency cleanup
        register_shutdown_function([$this, 'shutdownHandler']);
        
        // Register signal handlers for graceful shutdown
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'signalHandler']);
            pcntl_signal(SIGINT, [$this, 'signalHandler']);
        }
    }

    public function handleTerminalError(TerminalException $e): void
    {
        $this->logError('terminal', $e);
        
        // Terminal errors are often critical - try emergency cleanup
        if (!$e->isRecoverable()) {
            $this->emergencyCleanup();
            throw $e; // Re-throw fatal errors
        }
        
        // For recoverable errors, try to continue
        $this->attemptTerminalRecovery($e);
    }

    public function handleRenderError(RenderException $e): void
    {
        $this->logError('render', $e);
        
        // Rendering errors are usually recoverable
        // We can skip a frame and continue
        if ($this->logErrors) {
            error_log("TUI Render Error: " . $e->getMessage());
        }
    }

    public function handleTuiError(TuiException $e): void
    {
        $this->logError('tui', $e);
        
        if (!$e->isRecoverable()) {
            $this->emergencyCleanup();
            throw $e;
        }
    }

    public function handleUnexpectedError(Exception $e): void
    {
        $this->logError('unexpected', $e);
        
        // All unexpected errors are treated as fatal
        $this->emergencyCleanup();
        throw $e;
    }

    public function shouldContinue(Exception $e): bool
    {
        // If we're in emergency mode, don't continue
        if ($this->emergencyMode) {
            return false;
        }
        
        // Check if this is a TUI exception with recovery info
        if ($e instanceof TuiException) {
            return $e->isRecoverable();
        }
        
        // All other exceptions are fatal
        return false;
    }

    public function emergencyCleanup(): void
    {
        if ($this->emergencyMode) {
            return; // Prevent recursive cleanup
        }
        
        $this->emergencyMode = true;
        
        try {
            // Run registered cleanup handlers
            foreach ($this->cleanupHandlers as $handler) {
                try {
                    $handler();
                } catch (Exception $e) {
                    // Ignore cleanup handler errors during emergency
                }
            }
            
            // Terminal cleanup is critical
            if ($this->terminal) {
                try {
                    $this->terminal->disableMouseReporting();
                } catch (Exception $e) {
                    // Ignore - we're in emergency mode
                }
                
                try {
                    $this->terminal->disableRawMode();
                } catch (Exception $e) {
                    // Ignore - we're in emergency mode
                }
                
                try {
                    // Try to show cursor and reset colors
                    $this->terminal->write("\033[?25h\033[0m");
                } catch (Exception $e) {
                    // Ignore - we're in emergency mode
                }
            }
            
        } catch (Exception $e) {
            // Emergency cleanup failed - this is very bad
            // Try the most basic terminal reset
            echo "\033[?25h\033[0m"; // Show cursor, reset colors
        }
    }

    public function getErrorStats(): array
    {
        return [
            'total_errors' => count($this->errorHistory),
            'error_counts' => $this->errorCounts,
            'recent_errors' => array_slice($this->errorHistory, -5),
            'emergency_mode' => $this->emergencyMode,
        ];
    }

    public function registerCleanupHandler(callable $handler): void
    {
        $this->cleanupHandlers[] = $handler;
    }

    /**
     * PHP shutdown handler - catches fatal errors and ensures cleanup.
     */
    public function shutdownHandler(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->emergencyCleanup();
        }
    }

    /**
     * Signal handler for graceful shutdown.
     */
    public function signalHandler(int $signal): void
    {
        $this->emergencyCleanup();
        exit(0);
    }

    private function logError(string $type, Exception $e): void
    {
        $errorInfo = [
            'type' => $type,
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'timestamp' => microtime(true),
        ];
        
        $this->errorHistory[] = $errorInfo;
        $this->errorCounts[$type] = ($this->errorCounts[$type] ?? 0) + 1;
        
        // Keep only last 50 errors
        if (count($this->errorHistory) > 50) {
            array_shift($this->errorHistory);
        }
        
        if ($this->logErrors) {
            error_log("TUI {$type} Error: " . $e->getMessage());
        }
    }

    private function attemptTerminalRecovery(TerminalException $e): void
    {
        if (!$this->terminal) {
            return;
        }
        
        try {
            // Try to recover based on error type
            switch ($e->getCode()) {
                case 1002: // Size detection failed
                    // Continue with default size
                    break;
                case 1003: // Read failed
                    // Could try to reinitialize input
                    break;
                case 1004: // Write failed
                    // Could try to clear and continue
                    break;
                default:
                    // General recovery attempt
                    break;
            }
        } catch (Exception $recoveryError) {
            // Recovery failed - this might be fatal
            throw $e;
        }
    }
}