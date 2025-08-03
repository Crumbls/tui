<?php

declare(strict_types=1);

namespace Crumbls\Tui\Layout;

use Crumbls\Tui\Display\Area;

use Crumbls\Tui\Display\Areas;

interface ConstraintSolver
{
    /**
     * @param Constraint[] $constraints
     */
    public function solve(Layout $layout, Area $area, array $constraints): Areas;
}
