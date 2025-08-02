<?php

declare(strict_types=1);

namespace Crumbls\Tui\Rendering;

use Crumbls\Tui\Contracts\BufferInterface;

/**
 * Enhanced buffer implementation based on PhpTui's approach.
 * Uses cell-based storage with styling support and efficient diffing.
 */
class EnhancedBuffer implements BufferInterface
{
    /** @var Cell[] */
    private array $cells = [];

    public function __construct(
        private int $width = 80,
        private int $height = 24
    ) {
        $this->initializeCells();
    }

    public function write(int $x, int $y, string $content): void
    {
        if (!$this->isValidPosition($x, $y)) {
            return;
        }

        $lines = explode("\n", $content);
        $currentY = $y;

        foreach ($lines as $line) {
            if ($currentY >= $this->height) {
                break;
            }

            $this->writeLineAtPosition($x, $currentY, $line);
            $currentY++;
        }
    }

    public function writeStyledCell(int $x, int $y, Cell $cell): void
    {
        if (!$this->isValidPosition($x, $y)) {
            return;
        }

        $index = $this->coordinateToIndex($x, $y);
        $this->cells[$index] = $cell->clone();
    }

    public function clear(): void
    {
        $this->initializeCells();
    }

    public function getAt(int $x, int $y): string
    {
        if (!$this->isValidPosition($x, $y)) {
            return ' ';
        }

        $index = $this->coordinateToIndex($x, $y);
        return $this->cells[$index]->char;
    }

    public function getCellAt(int $x, int $y): Cell
    {
        if (!$this->isValidPosition($x, $y)) {
            return Cell::empty();
        }

        $index = $this->coordinateToIndex($x, $y);
        return $this->cells[$index];
    }

    public function getRegion(int $x, int $y, int $width, int $height): array
    {
        [$clippedX, $clippedY, $clippedWidth, $clippedHeight] = $this->clipToBounds($x, $y, $width, $height);
        
        $region = [];
        for ($currentY = $clippedY; $currentY < $clippedY + $clippedHeight; $currentY++) {
            $row = '';
            for ($currentX = $clippedX; $currentX < $clippedX + $clippedWidth; $currentX++) {
                $row .= $this->getAt($currentX, $currentY);
            }
            $region[] = $row;
        }
        
        return $region;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function resize(int $width, int $height): void
    {
        $oldCells = $this->cells;
        $oldWidth = $this->width;

        $this->width = max(1, $width);
        $this->height = max(1, $height);
        
        $this->initializeCells();

        // Copy old cells to new buffer
        foreach ($oldCells as $index => $cell) {
            [$oldX, $oldY] = $this->indexToCoordinate($index, $oldWidth);
            if ($this->isValidPosition($oldX, $oldY)) {
                $newIndex = $this->coordinateToIndex($oldX, $oldY);
                $this->cells[$newIndex] = $cell;
            }
        }
    }

    /**
     * Compute diff between this buffer and another buffer (PhpTui approach).
     */
    public function diff(EnhancedBuffer $other): BufferUpdates
    {
        $updates = new BufferUpdates();

        if ($this->width !== $other->width || $this->height !== $other->height) {
            // If dimensions differ, update everything
            for ($y = 0; $y < $other->height; $y++) {
                for ($x = 0; $x < $other->width; $x++) {
                    $cell = $other->getCellAt($x, $y);
                    $updates->add(new BufferUpdate($x, $y, $cell));
                }
            }
            return $updates;
        }

        // Compare cell by cell
        for ($i = 0; $i < count($other->cells); $i++) {
            $currentCell = $this->cells[$i] ?? Cell::empty();
            $nextCell = $other->cells[$i];

            if (!$currentCell->equals($nextCell)) {
                [$x, $y] = $this->indexToCoordinate($i, $this->width);
                $updates->add(new BufferUpdate($x, $y, $nextCell));
            }
        }

        return $updates;
    }

    public function copy(): self
    {
        $copy = new self($this->width, $this->height);
        $copy->cells = [];
        
        foreach ($this->cells as $cell) {
            $copy->cells[] = $cell->clone();
        }
        
        return $copy;
    }

    private function initializeCells(): void
    {
        $this->cells = [];
        $totalCells = $this->width * $this->height;
        
        for ($i = 0; $i < $totalCells; $i++) {
            $this->cells[] = Cell::empty();
        }
    }

    private function writeLineAtPosition(int $x, int $currentY, string $line): void
    {
        $currentX = $x;
        
        // Handle multi-byte characters properly
        $chars = mb_str_split($line);
        
        foreach ($chars as $char) {
            if ($currentX >= $this->width) {
                break;
            }

            if ($this->isValidPosition($currentX, $currentY)) {
                $index = $this->coordinateToIndex($currentX, $currentY);
                $this->cells[$index]->setChar($char);
            }
            $currentX++;
        }
    }

    private function coordinateToIndex(int $x, int $y): int
    {
        return $y * $this->width + $x;
    }

    private function indexToCoordinate(int $index, int $width): array
    {
        return [$index % $width, intval($index / $width)];
    }

    private function isValidPosition(int $x, int $y): bool
    {
        return $x >= 0 && $x < $this->width && $y >= 0 && $y < $this->height;
    }

    private function clipToBounds(int $x, int $y, int $width, int $height): array
    {
        $clippedX = max(0, min($x, $this->width - 1));
        $clippedY = max(0, min($y, $this->height - 1));
        $clippedWidth = max(0, min($width, $this->width - $clippedX));
        $clippedHeight = max(0, min($height, $this->height - $clippedY));
        
        return [$clippedX, $clippedY, $clippedWidth, $clippedHeight];
    }
}