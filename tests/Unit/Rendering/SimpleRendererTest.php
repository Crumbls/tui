<?php

declare(strict_types=1);

use Crumbls\Tui\Rendering\SimpleRenderer;

describe('SimpleRenderer', function () {
    test('creates renderer with default size', function () {
        $renderer = new SimpleRenderer();
        
        expect($renderer->isDirty())->toBeTrue();
    });

    test('sets size and marks dirty', function () {
        $renderer = new SimpleRenderer();
        
        $renderer->clearDirty();
        expect($renderer->isDirty())->toBeFalse();
        
        $renderer->setSize(100, 50);
        expect($renderer->isDirty())->toBeTrue();
    });

    test('adds content and marks dirty', function () {
        $renderer = new SimpleRenderer();
        
        $renderer->clearDirty();
        $renderer->addLine('Test line');
        
        expect($renderer->isDirty())->toBeTrue();
    });

    test('renders content with header and footer', function () {
        $renderer = new SimpleRenderer();
        $renderer->setSize(40, 10);
        $renderer->setContent(['Line 1', 'Line 2']);
        
        $output = $renderer->render();
        
        expect($output)->toContain('TUI Demo');
        expect($output)->toContain('Line 1');
        expect($output)->toContain('Line 2');
        expect($output)->toContain('Press \'q\' to quit');
    });

    test('clears content', function () {
        $renderer = new SimpleRenderer();
        $renderer->addLine('Test');
        $renderer->clearContent();
        
        $output = $renderer->render();
        expect($output)->not->toContain('Test');
    });

    test('manages dirty state', function () {
        $renderer = new SimpleRenderer();
        
        expect($renderer->isDirty())->toBeTrue();
        
        $renderer->clearDirty();
        expect($renderer->isDirty())->toBeFalse();
        
        $renderer->markDirty();
        expect($renderer->isDirty())->toBeTrue();
    });
});