<?php

declare(strict_types=1);

namespace Crumbls\Tui\Concerns;

use Crumbls\Tui\Contracts\PositionableInterface;

/**
 * Trait providing position and dimension management for components.
 */
trait HasPosition
{
    protected int $x = 0;
    protected int $y = 0;
    protected int $width = 1;
    protected int $height = 1;
    protected ?PositionableInterface $parent = null;

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getX2(): int
    {
        return $this->x + $this->width - 1;
    }

    public function getY2(): int
    {
        return $this->y + $this->height - 1;
    }

    public function setPosition(int $x, int $y): static
    {
        $this->x = max(0, $x);
        $this->y = max(0, $y);
        return $this;
    }

    public function setSize(int $width, int $height): static
    {
        $this->width = max(1, $width);
        $this->height = max(1, $height);
        return $this;
    }

    public function setBounds(int $x, int $y, int $width, int $height): static
    {
        return $this->setPosition($x, $y)->setSize($width, $height);
    }

    public function containsPoint(int $x, int $y): bool
    {
        $absX = $this->getAbsoluteX();
        $absY = $this->getAbsoluteY();
        
        return $x >= $absX && 
               $x <= $absX + $this->width - 1 &&
               $y >= $absY && 
               $y <= $absY + $this->height - 1;
    }

    public function overlaps(PositionableInterface $other): bool
    {
        $thisAbsX = $this->getAbsoluteX();
        $thisAbsY = $this->getAbsoluteY();
        $otherAbsX = $other->getAbsoluteX();
        $otherAbsY = $other->getAbsoluteY();

        return !($thisAbsX > $otherAbsX + $other->getWidth() - 1 ||
                $otherAbsX > $thisAbsX + $this->width - 1 ||
                $thisAbsY > $otherAbsY + $other->getHeight() - 1 ||
                $otherAbsY > $thisAbsY + $this->height - 1);
    }

    public function getAbsoluteX(): int
    {
        return $this->parent ? $this->parent->getAbsoluteX() + $this->x : $this->x;
    }

    public function getAbsoluteY(): int
    {
        return $this->parent ? $this->parent->getAbsoluteY() + $this->y : $this->y;
    }

    public function getParent(): ?PositionableInterface
    {
        return $this->parent;
    }

    public function setParent(?PositionableInterface $parent): static
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Helper method to move the component by a relative amount.
     */
    public function moveBy(int $deltaX, int $deltaY): static
    {
        return $this->setPosition($this->x + $deltaX, $this->y + $deltaY);
    }

    /**
     * Helper method to resize the component by a relative amount.
     */
    public function resizeBy(int $deltaWidth, int $deltaHeight): static
    {
        return $this->setSize($this->width + $deltaWidth, $this->height + $deltaHeight);
    }

    /**
     * Helper method to center this component within its parent.
     */
    public function centerInParent(): static
    {
        if (!$this->parent) {
            return $this;
        }

        $parentWidth = $this->parent->getWidth();
        $parentHeight = $this->parent->getHeight();
        
        $centerX = max(0, ($parentWidth - $this->width) / 2);
        $centerY = max(0, ($parentHeight - $this->height) / 2);
        
        return $this->setPosition((int)$centerX, (int)$centerY);
    }

    /**
     * Helper method to get bounds as an array.
     */
    public function getBounds(): array
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
            'width' => $this->width,
            'height' => $this->height,
            'x2' => $this->getX2(),
            'y2' => $this->getY2(),
        ];
    }

    /**
     * Helper method to get absolute bounds as an array.
     */
    public function getAbsoluteBounds(): array
    {
        $absX = $this->getAbsoluteX();
        $absY = $this->getAbsoluteY();
        
        return [
            'x' => $absX,
            'y' => $absY,
            'width' => $this->width,
            'height' => $this->height,
            'x2' => $absX + $this->width - 1,
            'y2' => $absY + $this->height - 1,
        ];
    }
}