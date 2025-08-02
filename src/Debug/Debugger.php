<?php

declare(strict_types=1);

namespace Crumbls\Tui\Debug;

use Crumbls\Tui\Contracts\DebuggerInterface;
use Crumbls\Tui\Contracts\EventInterface;
use Crumbls\Tui\Contracts\ComponentInterface;

/**
 * Debug information collector and analyzer.
 */
class Debugger implements DebuggerInterface
{
    private array $eventHistory = [];
    private array $renderHistory = [];
    private array $componentHistory = [];
    private array $performanceMetrics = [];
    private int $maxHistorySize = 1000;
    private bool $enabled = true;

    public function logEvent(EventInterface $event): void
    {
        if (!$this->enabled) {
            return;
        }

        $eventData = [
            'timestamp' => microtime(true),
            'type' => $event::class,
            'data' => method_exists($event, 'toArray') ? $event->toArray() : [],
            'memory' => memory_get_usage(true),
        ];

        $this->eventHistory[] = $eventData;
        $this->limitHistorySize($this->eventHistory);
    }

    public function logRender(string $region, float $time): void
    {
        if (!$this->enabled) {
            return;
        }

        $renderData = [
            'timestamp' => microtime(true),
            'region' => $region,
            'time' => $time,
            'memory' => memory_get_usage(true),
        ];

        $this->renderHistory[] = $renderData;
        $this->limitHistorySize($this->renderHistory);

        // Update performance metrics
        if (!isset($this->performanceMetrics['render'])) {
            $this->performanceMetrics['render'] = [
                'total_time' => 0,
                'count' => 0,
                'average_time' => 0,
                'max_time' => 0,
                'min_time' => PHP_FLOAT_MAX,
            ];
        }

        $metrics = &$this->performanceMetrics['render'];
        $metrics['total_time'] += $time;
        $metrics['count']++;
        $metrics['average_time'] = $metrics['total_time'] / $metrics['count'];
        $metrics['max_time'] = max($metrics['max_time'], $time);
        $metrics['min_time'] = min($metrics['min_time'], $time);
    }

    public function logComponent(ComponentInterface $component, string $action): void
    {
        if (!$this->enabled) {
            return;
        }

        $componentData = [
            'timestamp' => microtime(true),
            'component_id' => $component->getId(),
            'component_class' => $component::class,
            'action' => $action,
            'debug_info' => $component->getDebugInfo(),
        ];

        $this->componentHistory[] = $componentData;
        $this->limitHistorySize($this->componentHistory);
    }

    public function getEventHistory(): array
    {
        return $this->eventHistory;
    }

    public function getPerformanceMetrics(): array
    {
        return $this->performanceMetrics;
    }

    public function getComponentTree(): array
    {
        // Build component tree from component history
        $components = [];
        foreach ($this->componentHistory as $entry) {
            $id = $entry['component_id'];
            if (!isset($components[$id])) {
                $components[$id] = [
                    'id' => $id,
                    'class' => $entry['component_class'],
                    'actions' => [],
                    'latest_info' => null,
                ];
            }
            $components[$id]['actions'][] = [
                'timestamp' => $entry['timestamp'],
                'action' => $entry['action'],
            ];
            $components[$id]['latest_info'] = $entry['debug_info'];
        }

        return array_values($components);
    }

    public function clearLogs(): void
    {
        $this->eventHistory = [];
        $this->renderHistory = [];
        $this->componentHistory = [];
        $this->performanceMetrics = [];
    }

    public function setMaxHistorySize(int $size): void
    {
        $this->maxHistorySize = max(1, $size);
        $this->limitHistorySize($this->eventHistory);
        $this->limitHistorySize($this->renderHistory);
        $this->limitHistorySize($this->componentHistory);
    }

    public function getMaxHistorySize(): int
    {
        return $this->maxHistorySize;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getMemoryStats(): array
    {
        return [
            'current_usage' => memory_get_usage(true),
            'peak_usage' => memory_get_peak_usage(true),
            'current_usage_formatted' => $this->formatBytes(memory_get_usage(true)),
            'peak_usage_formatted' => $this->formatBytes(memory_get_peak_usage(true)),
        ];
    }

    public function getTimingStats(): array
    {
        $stats = [
            'render' => $this->performanceMetrics['render'] ?? null,
        ];

        // Calculate event processing stats
        if (!empty($this->eventHistory)) {
            $eventTimes = [];
            for ($i = 1; $i < count($this->eventHistory); $i++) {
                $timeDiff = $this->eventHistory[$i]['timestamp'] - $this->eventHistory[$i - 1]['timestamp'];
                $eventTimes[] = $timeDiff;
            }

            if (!empty($eventTimes)) {
                $stats['events'] = [
                    'average_interval' => array_sum($eventTimes) / count($eventTimes),
                    'max_interval' => max($eventTimes),
                    'min_interval' => min($eventTimes),
                ];
            }
        }

        return $stats;
    }

    public function exportDebugData(): array
    {
        return [
            'event_history' => $this->eventHistory,
            'render_history' => $this->renderHistory,
            'component_history' => $this->componentHistory,
            'performance_metrics' => $this->performanceMetrics,
            'memory_stats' => $this->getMemoryStats(),
            'timing_stats' => $this->getTimingStats(),
            'exported_at' => microtime(true),
        ];
    }

    public function importDebugData(array $data): void
    {
        $this->eventHistory = $data['event_history'] ?? [];
        $this->renderHistory = $data['render_history'] ?? [];
        $this->componentHistory = $data['component_history'] ?? [];
        $this->performanceMetrics = $data['performance_metrics'] ?? [];

        // Limit imported data to max history size
        $this->limitHistorySize($this->eventHistory);
        $this->limitHistorySize($this->renderHistory);
        $this->limitHistorySize($this->componentHistory);
    }

    private function limitHistorySize(array &$history): void
    {
        if (count($history) > $this->maxHistorySize) {
            $history = array_slice($history, -$this->maxHistorySize);
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}