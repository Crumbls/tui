<?php

declare(strict_types=1);

namespace Crumbls\Tui\Contracts;

/**
 * Interface for debugging and development tools.
 */
interface DebuggerInterface
{
    /**
     * Log an event for debugging purposes.
     */
    public function logEvent(EventInterface $event): void;

    /**
     * Log a render operation with timing information.
     */
    public function logRender(string $region, float $time): void;

    /**
     * Log component information.
     */
    public function logComponent(ComponentInterface $component, string $action): void;

    /**
     * Get the event history.
     */
    public function getEventHistory(): array;

    /**
     * Get performance metrics.
     */
    public function getPerformanceMetrics(): array;

    /**
     * Get component tree information.
     */
    public function getComponentTree(): array;

    /**
     * Clear all debug logs.
     */
    public function clearLogs(): void;

    /**
     * Set the maximum number of events to keep in history.
     */
    public function setMaxHistorySize(int $size): void;

    /**
     * Get the current history size limit.
     */
    public function getMaxHistorySize(): int;

    /**
     * Enable or disable debug logging.
     */
    public function setEnabled(bool $enabled): void;

    /**
     * Check if debug logging is enabled.
     */
    public function isEnabled(): bool;

    /**
     * Get memory usage statistics.
     */
    public function getMemoryStats(): array;

    /**
     * Get timing statistics.
     */
    public function getTimingStats(): array;

    /**
     * Export debug data to an array.
     */
    public function exportDebugData(): array;

    /**
     * Import debug data from an array.
     */
    public function importDebugData(array $data): void;
}