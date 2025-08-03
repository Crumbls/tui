<?php

declare(strict_types=1);

namespace Crumbls\Tui\Layout\Constraint;

use Crumbls\Tui\Layout\Constraint;

final class MinConstraint extends Constraint
{
    public function __construct(public int $min)
    {
    }

    public function __toString(): string
    {
        return sprintf('Min(%d)', $this->min);
    }

}
