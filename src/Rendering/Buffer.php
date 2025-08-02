<?php

declare(strict_types=1);

namespace Crumbls\Tui\Rendering;

use Crumbls\Tui\Contracts\BufferInterface;

/**
 * Screen buffer implementation with coordinate tracking and dirty regions.
 */
class Buffer implements BufferInterface
{
    private array $buffer = [];
    private array $dirtyRegions = [];
    private int $width;
    private int $height;

    public function __construct(int $width = 80, int $height = 24)
    {
        $this->width = $width;
        $this->height = $height;
        $this->clear();
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

            $currentX = $x;
            for ($i = 0; $i < strlen($line) && $currentX < $this->width; $i++) {
                if ($currentY >= 0 && $currentX >= 0) {
                    $this->buffer[$currentY][$currentX] = $line[$i];
                    $this->markDirty($currentX, $currentY, 1, 1);
                }
                $currentX++;
            }
            $currentY++;
        }
    }

    public function writeInRegion(int $x, int $y, int $width, int $height, string $content): void
    {
        [$clippedX, $clippedY, $clippedWidth, $clippedHeight] = $this->clipToBounds($x, $y, $width, $height);
        
        $lines = explode("\n", $content);
        $currentY = $clippedY;

        foreach ($lines as $lineIndex => $line) {
            if ($lineIndex >= $clippedHeight || $currentY >= $this->height) {
                break;
            }

            $currentX = $clippedX;
            $lineWidth = min(strlen($line), $clippedWidth);
            
            for ($i = 0; $i < $lineWidth && $currentX < $this->width; $i++) {
                if ($currentY >= 0 && $currentX >= 0) {
                    $this->buffer[$currentY][$currentX] = $line[$i];
                }
                $currentX++;
            }
            $currentY++;
        }

        $this->markDirty($clippedX, $clippedY, $clippedWidth, $clippedHeight);
    }

    public function clear(): void
    {
        $this->buffer = [];
        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                $this->buffer[$y][$x] = ' ';
            }
        }
        $this->markDirty(0, 0, $this->width, $this->height);
    }

    public function clearRegion(int $x, int $y, int $width, int $height): void
    {
        $this->fill($x, $y, $width, $height, ' ');
    }

    public function getAt(int $x, int $y): string
    {
        if (!$this->isValidPosition($x, $y)) {
            return ' ';
        }
        return $this->buffer[$y][$x] ?? ' ';
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
        $oldWidth = $this->width;
        $oldHeight = $this->height;
        
        $this->width = max(1, $width);
        $this->height = max(1, $height);

        // Resize buffer array
        $newBuffer = [];
        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                if ($y < $oldHeight && $x < $oldWidth) {
                    $newBuffer[$y][$x] = $this->buffer[$y][$x];
                } else {
                    $newBuffer[$y][$x] = ' ';
                }
            }
        }

        $this->buffer = $newBuffer;
        $this->markDirty(0, 0, $this->width, $this->height);
    }

    public function diff(BufferInterface $other): array
    {
        $changes = [];
        
        $maxWidth = max($this->width, $other->getWidth());
        $maxHeight = max($this->height, $other->getHeight());
        
        for ($y = 0; $y < $maxHeight; $y++) {
            for ($x = 0; $x < $maxWidth; $x++) {
                $thisChar = $this->getAt($x, $y);
                $otherChar = $other->getAt($x, $y);
                
                if ($thisChar !== $otherChar) {
                    $changes[] = [
                        'x' => $x,
                        'y' => $y,
                        'old' => $otherChar,
                        'new' => $thisChar,
                    ];
                }
            }
        }
        
        return $changes;
    }

    public function markDirty(int $x, int $y, int $width, int $height): void
    {
        [$clippedX, $clippedY, $clippedWidth, $clippedHeight] = $this->clipToBounds($x, $y, $width, $height);
        
        $this->dirtyRegions[] = [
            'x' => $clippedX,
            'y' => $clippedY,
            'width' => $clippedWidth,
            'height' => $clippedHeight,
        ];
    }

    public function isDirty(int $x, int $y, int $width, int $height): bool
    {
        foreach ($this->dirtyRegions as $region) {
            if ($this->regionsOverlap($x, $y, $width, $height, $region['x'], $region['y'], $region['width'], $region['height'])) {
                return true;
            }
        }
        return false;
    }

    public function getDirtyRegions(): array
    {
        return $this->dirtyRegions;
    }

    public function clearDirtyRegions(): void
    {
        $this->dirtyRegions = [];
    }

    public function copyFrom(BufferInterface $source): void
    {
        $this->resize($source->getWidth(), $source->getHeight());
        
        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                $this->buffer[$y][$x] = $source->getAt($x, $y);
            }
        }
        
        $this->markDirty(0, 0, $this->width, $this->height);
    }

    public function copyRegionFrom(
        BufferInterface $source, 
        int $sourceX, int $sourceY, int $width, int $height,
        int $destX, int $destY
    ): void {
        [$clippedDestX, $clippedDestY, $clippedWidth, $clippedHeight] = 
            $this->clipToBounds($destX, $destY, $width, $height);
        
        for ($y = 0; $y < $clippedHeight; $y++) {
            for ($x = 0; $x < $clippedWidth; $x++) {
                $sourceChar = $source->getAt($sourceX + $x, $sourceY + $y);
                $this->buffer[$clippedDestY + $y][$clippedDestX + $x] = $sourceChar;
            }
        }
        
        $this->markDirty($clippedDestX, $clippedDestY, $clippedWidth, $clippedHeight);
    }

    public function fill(int $x, int $y, int $width, int $height, string $char = ' '): void
    {
        [$clippedX, $clippedY, $clippedWidth, $clippedHeight] = $this->clipToBounds($x, $y, $width, $height);
        
        for ($currentY = $clippedY; $currentY < $clippedY + $clippedHeight; $currentY++) {
            for ($currentX = $clippedX; $currentX < $clippedX + $clippedWidth; $currentX++) {
                $this->buffer[$currentY][$currentX] = $char[0] ?? ' ';
            }
        }
        
        $this->markDirty($clippedX, $clippedY, $clippedWidth, $clippedHeight);
    }

    public function toString(): string
    {
        $output = '';
        for ($y = 0; $y < $this->height; $y++) {
            $line = '';
            for ($x = 0; $x < $this->width; $x++) {
                $line .= $this->buffer[$y][$x] ?? ' ';
            }
            $output .= rtrim($line) . "\n";
        }
        return rtrim($output);
    }

    public function isValidPosition(int $x, int $y): bool
    {
        return $x >= 0 && $x < $this->width && $y >= 0 && $y < $this->height;
    }

    public function clipToBounds(int $x, int $y, int $width, int $height): array
    {
        $x = max(0, $x);
        $y = max(0, $y);
        $width = min($width, $this->width - $x);
        $height = min($height, $this->height - $y);
        
        return [$x, $y, max(0, $width), max(0, $height)];
    }

    private function regionsOverlap(int $x1, int $y1, int $w1, int $h1, int $x2, int $y2, int $w2, int $h2): bool
    {
        return !($x1 >= $x2 + $w2 || $x2 >= $x1 + $w1 || $y1 >= $y2 + $h2 || $y2 >= $y1 + $h1);
    }
}