<?php

use Crumbls\Tui\Components\Component;
use Crumbls\Tui\Components\Concerns\NestedSet;

class ComponentStub extends Component
{
    public function render(): string
    {
        return "ComponentStub[{$this->getId()}]";
    }
}
