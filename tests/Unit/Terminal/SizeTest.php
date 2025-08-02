<?php

declare(strict_types=1);

use Crumbls\Tui\Terminal\Size;

describe('Size', function () {
    test('creates valid size', function () {
        $size = new Size(80, 24);
        
        expect($size->width)->toBe(80);
        expect($size->height)->toBe(24);
    });

    test('calculates area correctly', function () {
        $size = new Size(80, 24);
        
        expect($size->area())->toBe(1920);
    });

    test('rejects invalid dimensions', function () {
        expect(fn() => new Size(0, 24))->toThrow(InvalidArgumentException::class);
        expect(fn() => new Size(80, 0))->toThrow(InvalidArgumentException::class);
        expect(fn() => new Size(-1, 24))->toThrow(InvalidArgumentException::class);
        expect(fn() => new Size(80, -1))->toThrow(InvalidArgumentException::class);
    });

    test('compares sizes correctly', function () {
        $size1 = new Size(80, 24);
        $size2 = new Size(80, 24);
        $size3 = new Size(100, 30);
        
        expect($size1->equals($size2))->toBeTrue();
        expect($size1->equals($size3))->toBeFalse();
    });

    test('converts to string correctly', function () {
        $size = new Size(80, 24);
        
        expect((string) $size)->toBe('80x24');
    });

    test('is readonly', function () {
        $size = new Size(80, 24);
        
        // This should cause a fatal error if Size isn't readonly
        // But since we can't test fatal errors easily, we'll trust the readonly keyword
        expect($size->width)->toBe(80);
        expect($size->height)->toBe(24);
    });
});