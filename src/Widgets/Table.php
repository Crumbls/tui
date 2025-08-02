<?php

declare(strict_types=1);

namespace Crumbls\Tui\Widgets;

use Crumbls\Tui\Concerns\HasFocus;
use Crumbls\Tui\Concerns\RendersFocusBorder;
use Crumbls\Tui\Contracts\FocusableInterface;
use Crumbls\Tui\Style\ColorTheme;
use Crumbls\Tui\Style\FocusStyle;
use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Table\Column;
use Crumbls\Tui\Table\TableSort;
use Crumbls\Tui\Widget;
use Illuminate\Support\Collection;

class Table extends Widget implements FocusableInterface
{
    use HasFocus, RendersFocusBorder;
    
    protected ?int $fitWidth = null;
    protected int $pageSize = 10;
    protected int $currentPage = 0;
    protected array $rowActions = [];
    protected array $selectedRows = [];
    protected array $columns = [];
    protected TableSort $sort;
    protected ?\Closure $queryCallback = null;
    protected ?\Closure $sortCallback = null;

    public function __construct()
    {
        $this->sort = new TableSort();
    }

    public function headers(array $headers): static
    {
        return $this->setAttribute('headers', $headers);
    }

    public function rows(array $rows): static
    {
        return $this->setAttribute('rows', $rows);
    }

    public function data(Collection|array $data): static
    {
        $rows = $data instanceof Collection ? $data->toArray() : $data;
        
        return $this->setAttribute('rows', $rows);
    }

    public function headerStyle(Style $style): static
    {
        return $this->setAttribute('header_style', $style);
    }

    public function rowStyle(Style $style): static
    {
        return $this->setAttribute('row_style', $style);
    }

    public function highlightStyle(Style $style): static
    {
        return $this->setAttribute('highlight_style', $style);
    }

    public function selectedRow(int $index): static
    {
        return $this->setAttribute('selected_row', $index);
    }

    public function columnWidths(array $widths): static
    {
        return $this->setAttribute('column_widths', $widths);
    }

    public function fitToWidth(?int $width = null): static
    {
        $this->fitWidth = $width;
        return $this;
    }

    public function pageSize(int $size): static
    {
        $this->pageSize = max(1, $size);
        return $this;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getTotalPages(): int
    {
        $rows = $this->getAttribute('rows', []);
        return max(1, intval(ceil(count($rows) / $this->pageSize)));
    }

    /**
     * Add a row action that triggers on a specific key.
     */
    public function onRowAction(string $key, callable $callback, string $description = ''): static
    {
        $this->rowActions[$key] = [
            'callback' => $callback,
            'description' => $description,
        ];
        return $this;
    }

    /**
     * Add primary action (Enter key).
     */
    public function onRowSelect(callable $callback): static
    {
        return $this->onRowAction("\n", $callback, 'Select row');
    }

    /**
     * Add secondary action (Space key).
     */
    public function onRowToggle(callable $callback): static
    {
        return $this->onRowAction(' ', $callback, 'Toggle row');
    }

    /**
     * Enable multi-selection mode.
     */
    public function multiSelect(bool $enabled = true): static
    {
        $this->setAttribute('multi_select', $enabled);
        return $this;
    }

    /**
     * Get selected rows (for multi-select).
     */
    public function getSelectedRows(): array
    {
        return $this->selectedRows;
    }

    /**
     * Check if a row is selected (for multi-select).
     */
    public function isRowSelected(int $rowIndex): bool
    {
        return in_array($rowIndex, $this->selectedRows);
    }

    /**
     * Set columns using Filament-style Column objects.
     */
    public function columns(array $columns): static
    {
        $this->columns = $columns;
        
        // Auto-generate headers from columns
        $headers = array_map(fn(Column $column) => $column->getLabel(), $columns);
        $this->setAttribute('headers', $headers);
        
        return $this;
    }

    /**
     * Set a database query callback for data loading.
     */
    public function query(\Closure $callback): static
    {
        $this->queryCallback = $callback;
        return $this;
    }

    /**
     * Set a custom sort callback for database queries.
     */
    public function onSort(\Closure $callback): static
    {
        $this->sortCallback = $callback;
        return $this;
    }

    /**
     * Set default sorting.
     */
    public function defaultSort(string $column, string $direction = 'asc'): static
    {
        $this->sort->by($column, $direction);
        return $this;
    }

    /**
     * Get current sort state.
     */
    public function getSort(): TableSort
    {
        return $this->sort;
    }

    public function setRegion(int $width, int $height): static
    {
        // Called by layout/parent to set available space
        $this->fitToWidth($width);
        return $this;
    }

    public function handleKey(string $key): bool
    {
        if (!$this->hasFocus()) {
            return false;
        }

        $rows = $this->getAttribute('rows', []);
        $selectedRow = $this->getAttribute('selected_row', 0);
        $totalRows = count($rows);
        $maxPage = max(0, intval(ceil($totalRows / $this->pageSize) - 1));
        
        // Check for row actions first
        if (isset($this->rowActions[$key])) {
            return $this->executeRowAction($key, $selectedRow, $rows[$selectedRow] ?? null);
        }
        
        $handled = match ($key) {
            'j', "\033[B" => $this->moveDown($selectedRow, $totalRows), // Down arrow or 'j'
            'k', "\033[A" => $this->moveUp($selectedRow), // Up arrow or 'k'
            "\033[6~" => $this->pageDown($maxPage), // Page Down (removed Space to allow for toggle)
            "\033[5~", 'b' => $this->pageUp(), // Page Up or 'b'
            'g' => $this->goToTop(), // Go to top
            'G' => $this->goToBottom($totalRows), // Go to bottom
            's' => $this->toggleSort(), // Sort current column
            default => false,
        };
        
        return $handled;
    }

    protected function moveDown(int $currentRow, int $totalRows): bool
    {
        $newRow = min($currentRow + 1, $totalRows - 1);
        if ($newRow !== $currentRow) {
            $this->selectedRow($newRow);
            $this->ensureRowVisible($newRow);
            return true;
        }
        return false;
    }

    protected function moveUp(int $currentRow): bool
    {
        $newRow = max($currentRow - 1, 0);
        if ($newRow !== $currentRow) {
            $this->selectedRow($newRow);
            $this->ensureRowVisible($newRow);
            return true;
        }
        return false;
    }

    protected function pageDown(int $maxPage): bool
    {
        $newPage = min($this->currentPage + 1, $maxPage);
        if ($newPage !== $this->currentPage) {
            $this->currentPage = $newPage;
            $this->selectedRow($this->currentPage * $this->pageSize);
            return true;
        }
        return false;
    }

    protected function pageUp(): bool
    {
        $newPage = max($this->currentPage - 1, 0);
        if ($newPage !== $this->currentPage) {
            $this->currentPage = $newPage;
            $this->selectedRow($this->currentPage * $this->pageSize);
            return true;
        }
        return false;
    }

    protected function goToTop(): bool
    {
        $this->selectedRow(0);
        $this->currentPage = 0;
        return true;
    }

    protected function goToBottom(int $totalRows): bool
    {
        $lastRow = max(0, $totalRows - 1);
        $this->selectedRow($lastRow);
        $this->currentPage = intval($lastRow / $this->pageSize);
        return true;
    }

    protected function ensureRowVisible(int $row): void
    {
        $requiredPage = intval($row / $this->pageSize);
        if ($requiredPage !== $this->currentPage) {
            $this->currentPage = $requiredPage;
        }
    }

    /**
     * Execute a row action callback.
     */
    protected function executeRowAction(string $key, int $rowIndex, ?array $rowData): bool
    {
        if (!isset($this->rowActions[$key])) {
            return false;
        }

        $action = $this->rowActions[$key];
        
        // Handle special multi-select toggle for space key
        if ($key === ' ' && $this->getAttribute('multi_select', false)) {
            $this->toggleRowSelection($rowIndex);
        }

        // Execute the callback
        try {
            $callback = $action['callback'];
            $callback($rowIndex, $rowData, $this);
            return true;
        } catch (\Exception $e) {
            // Handle callback errors gracefully
            return false;
        }
    }

    /**
     * Toggle selection for a row (multi-select mode).
     */
    protected function toggleRowSelection(int $rowIndex): void
    {
        $key = array_search($rowIndex, $this->selectedRows);
        if ($key !== false) {
            // Deselect
            unset($this->selectedRows[$key]);
            $this->selectedRows = array_values($this->selectedRows); // Re-index
        } else {
            // Select
            $this->selectedRows[] = $rowIndex;
        }
    }

    /**
     * Toggle sorting on the first sortable column.
     * In a more advanced version, this could cycle through columns.
     */
    protected function toggleSort(): bool
    {
        if (empty($this->columns)) {
            return false;
        }

        // Find first sortable column
        foreach ($this->columns as $column) {
            if ($column instanceof Column && $column->isSortable()) {
                $this->sort->toggle($column->getSortColumn());
                $this->refreshData();
                $this->currentPage = 0; // Reset to first page after sort
                return true;
            }
        }

        return false;
    }

    /**
     * Refresh table data after sort/filter changes.
     */
    protected function refreshData(): void
    {
        if ($this->queryCallback) {
            // Database-driven data
            $this->refreshFromQuery();
        } else {
            // Array-based data
            $this->refreshFromArray();
        }
    }

    /**
     * Refresh data from database query.
     */
    protected function refreshFromQuery(): void
    {
        if (!$this->queryCallback) {
            return;
        }

        $query = ($this->queryCallback)();
        
        // Apply sorting via callback
        if ($this->sort->isActive() && $this->sortCallback) {
            $sortData = $this->sort->getDatabaseSort();
            $query = ($this->sortCallback)($query, $sortData['column'], $sortData['direction']);
        }

        // Convert query result to array format
        // This would depend on your ORM - Eloquent, raw arrays, etc.
        // For now, assume it returns an array
        $data = $query;
        if (method_exists($query, 'toArray')) {
            $data = $query->toArray();
        }

        $this->setAttribute('rows', $data);
    }

    /**
     * Refresh data from array with sorting applied.
     */
    protected function refreshFromArray(): void
    {
        $originalData = $this->getAttribute('original_rows', []);
        if (empty($originalData)) {
            // Store original data for sorting
            $originalData = $this->getAttribute('rows', []);
            $this->setAttribute('original_rows', $originalData);
        }

        $sortedData = $this->sort->applyToArray($originalData, $this->columns);
        $this->setAttribute('rows', $sortedData);
    }

    public function render(): string
    {
        $headers = $this->getAttribute('headers', []);
        $rows = $this->getAttribute('rows', []);
        $selectedRow = $this->getAttribute('selected_row');
        $columnWidths = $this->getAttribute('column_widths', []);

        if (empty($headers) && empty($rows)) {
            return '';
        }

        // Format rows for consistent width calculation
        $formattedRows = [];
        if (!empty($this->columns)) {
            foreach ($rows as $row) {
                $formattedRows[] = $this->formatRowData($row);
            }
        } else {
            $formattedRows = $rows;
        }

        // If fitWidth set, override columnWidths to fit container
        if ($this->fitWidth !== null) {
            $count = max(count($headers), count($formattedRows[0] ?? []));
            if ($count > 0) {
                // Each column: width + 1 for left border, plus 1 for right border at end
                $totalBorders = $count + 1;
                $fit = max(1, intdiv(max(1, $this->fitWidth - $totalBorders), $count));
                $extra = max(0, ($this->fitWidth - $totalBorders) - ($fit * $count));
                $columnWidths = array_fill(0, $count, $fit);
                // Distribute remainder to leftmost columns
                for ($i = 0; $i < $extra; $i++) {
                    $columnWidths[$i % $count]++;
                }
            }
        } elseif (empty($columnWidths)) {
            $columnWidths = $this->calculateColumnWidths($headers, $formattedRows);
        }

        $output = '';

        // Render headers with sort indicators
        if (!empty($headers)) {
            $headerRow = $this->buildHeaderRow($headers);
            $output .= $this->renderRow($headerRow, $columnWidths, true, false, false);
            $output .= $this->renderSeparator($columnWidths);
        }

        // Render rows (with pagination)
        $startRow = $this->currentPage * $this->pageSize;
        $endRow = min($startRow + $this->pageSize, count($formattedRows));
        
        for ($index = $startRow; $index < $endRow; $index++) {
            if (!isset($formattedRows[$index])) break;
            $isSelected = $selectedRow === $index;
            $isMultiSelected = $this->isRowSelected($index);
            
            // Use already formatted row data
            $output .= $this->renderRow($formattedRows[$index], $columnWidths, false, $isSelected, $isMultiSelected);
        }

        // Add pagination info if needed
        if ($this->getTotalPages() > 1) {
            $output .= $this->renderPaginationInfo($columnWidths);
        }

        // Add focus styling that preserves table formatting
        if ($this->hasFocus()) {
            $focusIndicator = \Crumbls\Tui\Style\FocusStyle::renderFocusLabel('Table', true);
            
            // Calculate total table width for border
            $tableWidth = 0;
            if (!empty($columnWidths)) {
                $tableWidth = array_sum($columnWidths) + count($columnWidths) - 1; // Include column separators
            }
            
            $border = str_repeat('═', max($tableWidth, 50));
            
            return $focusIndicator . "\n" 
                 . ColorTheme::apply('table_border', $border) . "\n"
                 . $output 
                 . ColorTheme::apply('table_border', $border) . "\n";
        }
        
        return $output;
    }

    protected function calculateColumnWidths(array $headers, array $rows): array
    {
        $widths = [];
        $maxColumns = max(count($headers), ...array_map('count', $rows));

        for ($i = 0; $i < $maxColumns; $i++) {
            // Calculate header width including potential sort indicators
            $headerWidth = 0;
            if (isset($headers[$i])) {
                $headerText = (string) $headers[$i];
                
                // Add extra space for sort indicators if this column is sortable
                if (isset($this->columns[$i]) && $this->columns[$i] instanceof Column && $this->columns[$i]->isSortable()) {
                    $headerText .= ' ↓'; // Reserve space for the widest indicator
                }
                
                $headerWidth = $this->getDisplayWidth($headerText);
            }
            
            $maxRowWidth = 0;
            foreach ($rows as $row) {
                if (isset($row[$i])) {
                    $maxRowWidth = max($maxRowWidth, $this->getDisplayWidth((string) $row[$i]));
                }
            }

            $widths[$i] = max($headerWidth, $maxRowWidth) + 2; // Add padding (handled in renderRow)
        }

        return $widths;
    }

    /**
     * Get the display width of text, excluding ANSI escape codes.
     */
    protected function getDisplayWidth(string $text): int
    {
        // Remove ANSI escape sequences to get actual display width
        $cleanText = preg_replace('/\033\[[0-9;]*m/', '', $text);
        return mb_strlen($cleanText);
    }

    /**
     * Truncate text to a specific display width while preserving ANSI codes.
     */
    protected function truncateToDisplayWidth(string $text, int $maxWidth): string
    {
        if ($this->getDisplayWidth($text) <= $maxWidth) {
            return $text;
        }

        // If the text contains ANSI codes, we need to carefully truncate
        if (strpos($text, "\033[") !== false) {
            $result = '';
            $displayWidth = 0;
            $i = 0;
            $len = strlen($text);

            while ($i < $len && $displayWidth < $maxWidth) {
                if ($text[$i] === "\033" && $i + 1 < $len && $text[$i + 1] === '[') {
                    // Found ANSI escape sequence, find the end
                    $j = $i + 2;
                    while ($j < $len && !ctype_alpha($text[$j])) {
                        $j++;
                    }
                    if ($j < $len) {
                        $j++; // Include the final letter
                        $result .= substr($text, $i, $j - $i);
                        $i = $j;
                    } else {
                        break;
                    }
                } else {
                    $result .= $text[$i];
                    $displayWidth++;
                    $i++;
                }
            }
            return $result;
        }

        // Simple truncation for text without ANSI codes
        return mb_substr($text, 0, $maxWidth);
    }

    /**
     * Pad text to a specific display width while preserving ANSI codes.
     */
    protected function padToDisplayWidth(string $text, int $targetWidth): string
    {
        $displayWidth = $this->getDisplayWidth($text);
        $padding = max(0, $targetWidth - $displayWidth);
        return $text . str_repeat(' ', $padding);
    }

    protected function renderRow(array $row, array $widths, bool $isHeader = false, bool $isSelected = false, bool $isMultiSelected = false): string
    {
        $output = '';
        
        // Add selection indicator for multi-select mode
        if ($this->getAttribute('multi_select', false)) {
            if ($isHeader) {
                $output .= '  '; // Header spacing for selection column
            } else {
                $indicator = $isMultiSelected ? '●' : ' ';
                $output .= $indicator . ' ';
            }
        }
        
        $totalColumns = count($widths);
        for ($i = 0; $i < $totalColumns; $i++) {
            $width = $widths[$i] ?? 10;
            $cell = $row[$i] ?? '';
            
            // Ensure we don't exceed the column width, and pad to exact width
            $cellText = $this->truncateToDisplayWidth((string) $cell, $width - 2); // Reserve 2 chars for padding
            $cellContent = ' ' . $this->padToDisplayWidth($cellText, $width - 2) . ' '; // Add padding on both sides
            
            if ($isSelected) {
                // Use subtle selection indicators instead of background colors
                if ($i === 0) {
                    // Only show the selection arrow in the first column
                    $cellContent = '▶' . substr($cellContent, 1); // Replace first space with arrow
                }
                // Don't apply background colors - keep original content styling
            } elseif ($isMultiSelected) {
                // Show multi-selected rows with a different style
                $cellContent = ColorTheme::apply('success', $cellContent);
            } elseif ($isHeader) {
                $cellContent = ColorTheme::apply('table_header', $cellContent);
            }
            
            $output .= $cellContent;
            if ($i < $totalColumns - 1) {
                $output .= '│';
            }
        }
        return $output . "\n";
    }

    protected function renderSeparator(array $widths): string
    {
        $output = '';
        
        // Add separator for selection column in multi-select mode
        if ($this->getAttribute('multi_select', false)) {
            $output .= '──'; // Width for selection indicator
        }
        
        $count = count($widths);
        for ($i = 0; $i < $count; $i++) {
            $output .= str_repeat('─', $widths[$i]);
            if ($i < $count - 1) {
                $output .= '┼';
            }
        }
        return $output . "\n";
    }

    protected function renderPaginationInfo(array $widths): string
    {
        $totalWidth = array_sum($widths) + count($widths) - 1; // Include separators
        
        // Add width for selection column in multi-select mode
        if ($this->getAttribute('multi_select', false)) {
            $totalWidth += 2; // Space for selection indicator
        }
        $currentPage = $this->currentPage + 1; // 1-based for display
        $totalPages = $this->getTotalPages();
        $rows = $this->getAttribute('rows', []);
        $totalRows = count($rows);
        
        $info = "Page $currentPage/$totalPages ($totalRows rows)";
        
        if ($this->hasFocus()) {
            $info .= " • ↑↓:navigate PgUp/PgDn:page g/G:top/bottom Tab:switch focus";
            
            // Add action info
            if (!empty($this->rowActions)) {
                $actionInfo = [];
                foreach ($this->rowActions as $key => $action) {
                    $keyName = match($key) {
                        "\n" => 'Enter',
                        ' ' => 'Space',
                        default => $key
                    };
                    if (!empty($action['description'])) {
                        $actionInfo[] = $keyName . ':' . $action['description'];
                    }
                }
                if (!empty($actionInfo)) {
                    $info .= ' • ' . implode(' ', $actionInfo);
                }
            }
            
            $info = ColorTheme::apply('table_pagination', $info);
        }
        
        $padding = max(0, $totalWidth - mb_strlen(strip_tags($info)));
        return str_repeat('─', $totalWidth) . "\n" . $info . str_repeat(' ', $padding) . "\n";
    }

    /**
     * Build header row with sort indicators.
     */
    protected function buildHeaderRow(array $headers): array
    {
        $headerRow = [];
        
        foreach ($headers as $index => $header) {
            $headerText = (string) $header;
            
            // Add sort indicator if this column is sortable
            if (isset($this->columns[$index])) {
                $column = $this->columns[$index];
                if ($column instanceof Column && $column->isSortable()) {
                    $sortColumn = $column->getSortColumn();
                    $indicator = $this->sort->getSortIndicator($sortColumn);
                    $headerText .= $indicator;
                    
                    // Pad to the reserved width if no indicator is shown
                    if (empty($indicator)) {
                        $headerText .= '  '; // Two spaces for ' ↓'
                    }
                }
            }
            
            $headerRow[] = $headerText;
        }
        
        return $headerRow;
    }

    /**
     * Format row data using column definitions.
     */
    protected function formatRowData(array $rowData): array
    {
        if (empty($this->columns)) {
            return $rowData; // Fallback to original data
        }

        $formattedRow = [];
        foreach ($this->columns as $column) {
            if ($column instanceof Column) {
                $value = $column->getValue($rowData);
                $formattedValue = $column->formatValue($value, $rowData);
                $formattedRow[] = $formattedValue;
            }
        }

        return $formattedRow;
    }
}