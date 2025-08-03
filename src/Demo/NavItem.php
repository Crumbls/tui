<?php

declare(strict_types=1);

namespace Crumbls\Tui\Demo;

final readonly class NavItem
{
    public function __construct(
        public string $key,
        public string $label,
    ) {
    }
}