<?php

declare(strict_types=1);

namespace Crumbls\Tui\Text;

use Generator;
use Crumbls\Tui\Widget\HorizontalAlignment;

interface LineComposer
{
    /**
     * @return Generator<array{list<StyledGrapheme>, int, HorizontalAlignment}>
     */
    public function nextLine(): Generator;
}
