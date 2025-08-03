<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Shape;

use Crumbls\Tui\Extension\Core\Shape\Data\MapData;

enum MapResolution
{
    case High;
    case Low;

    /**
     * @return list<array{float,float}>
     */
    public function data(): array
    {
        return match ($this) {
            self::High => MapData::high(),
            self::Low => MapData::low(),
        };
    }
}
