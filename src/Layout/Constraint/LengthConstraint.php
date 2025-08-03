<?php

declare(strict_types=1);

namespace Crumbls\Tui\Layout\Constraint;

use Crumbls\Tui\Layout\Constraint;

final class LengthConstraint extends Constraint
{
    public function __construct(public int $length)
    {
    }

    public function __toString(): string
    {
        return sprintf('Length(%d)', $this->length);
    }
}
