<?php

declare(strict_types=1);

namespace Crumbls\Tui\Text;

use Crumbls\Tui\Style\Style;

final class StyledGrapheme
{
    public function __construct(
        public string $symbol,
        public Style $style
    ) {
    }

    /**
     * @return int<0,max>
     */
    public function symbolWidth(): int
    {
        return mb_strwidth($this->symbol);
    }
}
