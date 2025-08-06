<?php

namespace Crumbls\Tui\Components\Contracts;

interface Component
{
    /**
     * Get component unique identifier
     */
    public function getId(): string;
    
    /**
     * Set component unique identifier
     */
    public function setId(string $id): self;
    
    /**
     * Render the component
     */
    public function render(): string;

    /**
     * Get component depth in tree (root = 0)
     */
    public function getDepth(): int;
}