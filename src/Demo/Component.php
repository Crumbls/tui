<?php

declare(strict_types=1);

namespace Crumbls\Tui\Demo;

use Crumbls\Tui\Widget\Widget;

interface Component
{
    public function build(): Widget;

    public function handle(mixed $event): void;
}