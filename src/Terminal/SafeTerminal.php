<?php

declare(strict_types=1);

namespace Crumbls\Tui\Terminal;

use Crumbls\Tui\Contracts\TerminalInterface;
use Crumbls\Tui\Contracts\ErrorHandlerInterface;
use Crumbls\Tui\Exceptions\TerminalException;
use Exception;

/**
 * Error-safe terminal wrapper that handles failures gracefully.
 */
class SafeTerminal implements TerminalInterface
{
    public function __construct(
        private TerminalInterface $terminal,
        private ErrorHandlerInterface $errorHandler
    ) {
        // Register terminal cleanup with error handler
        $this->errorHandler->registerCleanupHandler(function () {
            try {
                $this->terminal->disableMouseReporting();
                $this->terminal->disableRawMode();
            } catch (Exception $e) {
                // Ignore cleanup errors
            }
        });
    }

    public function getSize(): Size
    {
        try {
            return $this->terminal->getSize();
        } catch (Exception $e) {
            $terminalException = TerminalException::sizeDetectionFailed($e);
            $this->errorHandler->handleTerminalError($terminalException);
            
            // Return default size as fallback
            return new Size(80, 24);
        }
    }

    public function readKey(float $timeout = 0): ?string
    {
        try {
            return $this->terminal->readKey($timeout);
        } catch (Exception $e) {
            $terminalException = TerminalException::readFailed($e->getMessage(), $e);
            $this->errorHandler->handleTerminalError($terminalException);
            return null;
        }
    }

    public function write(string $content): void
    {
        try {
            $this->terminal->write($content);
        } catch (Exception $e) {
            $terminalException = TerminalException::writeFailed($e->getMessage(), $e);
            $this->errorHandler->handleTerminalError($terminalException);
        }
    }

    public function clear(): void
    {
        try {
            $this->terminal->clear();
        } catch (Exception $e) {
            $terminalException = TerminalException::writeFailed("clear failed: " . $e->getMessage(), $e);
            $this->errorHandler->handleTerminalError($terminalException);
        }
    }

    public function enableRawMode(): void
    {
        try {
            $this->terminal->enableRawMode();
        } catch (Exception $e) {
            $terminalException = TerminalException::rawModeFailed("enable", $e);
            $this->errorHandler->handleTerminalError($terminalException);
        }
    }

    public function disableRawMode(): void
    {
        try {
            $this->terminal->disableRawMode();
        } catch (Exception $e) {
            $terminalException = TerminalException::rawModeFailed("disable", $e);
            $this->errorHandler->handleTerminalError($terminalException);
        }
    }

    public function supportsColors(): bool
    {
        try {
            return $this->terminal->supportsColors();
        } catch (Exception $e) {
            // If we can't detect color support, assume false
            return false;
        }
    }

    public function supportsMouse(): bool
    {
        try {
            return $this->terminal->supportsMouse();
        } catch (Exception $e) {
            // If we can't detect mouse support, assume false
            return false;
        }
    }

    public function enableMouseReporting(): void
    {
        try {
            $this->terminal->enableMouseReporting();
        } catch (Exception $e) {
            $terminalException = TerminalException::mouseReportingFailed("enable", $e);
            $this->errorHandler->handleTerminalError($terminalException);
        }
    }

    public function disableMouseReporting(): void
    {
        try {
            $this->terminal->disableMouseReporting();
        } catch (Exception $e) {
            $terminalException = TerminalException::mouseReportingFailed("disable", $e);
            $this->errorHandler->handleTerminalError($terminalException);
        }
    }

    public function queue(string $command): void
    {
        try {
            $this->terminal->queue($command);
        } catch (Exception $e) {
            $terminalException = TerminalException::writeFailed("queue failed: " . $e->getMessage(), $e);
            $this->errorHandler->handleTerminalError($terminalException);
        }
    }

    public function flush(): void
    {
        try {
            $this->terminal->flush();
        } catch (Exception $e) {
            $terminalException = TerminalException::writeFailed("flush failed: " . $e->getMessage(), $e);
            $this->errorHandler->handleTerminalError($terminalException);
        }
    }

    public function moveCursor(int $x, int $y): void
    {
        try {
            $this->terminal->moveCursor($x, $y);
        } catch (Exception $e) {
            $terminalException = TerminalException::writeFailed("moveCursor failed: " . $e->getMessage(), $e);
            $this->errorHandler->handleTerminalError($terminalException);
        }
    }

    public function getCursorPosition(): ?array
    {
        try {
            return $this->terminal->getCursorPosition();
        } catch (Exception $e) {
            $terminalException = TerminalException::readFailed("getCursorPosition failed: " . $e->getMessage(), $e);
            $this->errorHandler->handleTerminalError($terminalException);
            return null;
        }
    }

    public function setForegroundColor(int $r, int $g, int $b): void
    {
        try {
            $this->terminal->setForegroundColor($r, $g, $b);
        } catch (Exception $e) {
            $terminalException = TerminalException::writeFailed("setForegroundColor failed: " . $e->getMessage(), $e);
            $this->errorHandler->handleTerminalError($terminalException);
        }
    }

    public function setBackgroundColor(int $r, int $g, int $b): void
    {
        try {
            $this->terminal->setBackgroundColor($r, $g, $b);
        } catch (Exception $e) {
            $terminalException = TerminalException::writeFailed("setBackgroundColor failed: " . $e->getMessage(), $e);
            $this->errorHandler->handleTerminalError($terminalException);
        }
    }

    public function resetColors(): void
    {
        try {
            $this->terminal->resetColors();
        } catch (Exception $e) {
            $terminalException = TerminalException::writeFailed("resetColors failed: " . $e->getMessage(), $e);
            $this->errorHandler->handleTerminalError($terminalException);
        }
    }

    /**
     * Get the underlying terminal for direct access if needed.
     */
    public function getUnderlying(): TerminalInterface
    {
        return $this->terminal;
    }
}