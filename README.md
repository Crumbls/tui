# Crumbls TUI

A Laravel package for creating Terminal User Interface (TUI) components.

## Installation

Install the package via Composer:

```bash
composer require crumbls/tui
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=tui-config
```

## Usage

This package provides components for building terminal user interfaces in Laravel applications.

## Configuration

After publishing the configuration file, you can customize the TUI settings in `config/tui.php`.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

---

# Laravel TUI (Terminal User Interface)

A modern, Laravel-native TUI framework for building interactive terminal apps with expressive, Eloquent-style APIs and robust, responsive layouts.

## Vision
- Declarative, fluent API for composing TUIs (like Blade for the terminal)
- Nested, constraint-based layouts (vertical, horizontal, sidebar, etc.)
- Framework-managed redraw/input loop—users only declare UI and handle actions
- Responsive: Widgets always fit the terminal viewport, adapt to resize
- Extensible: Easy to create custom widgets and layouts
- Professional UX: Uses alternate screen buffer, raw mode, and robust rendering

## Example Usage
```php
Tui::screen(function ($screen) {
    $screen->layout('vertical', [
        Tui::navbar()->tabs(['Home', 'Settings']),
        $screen->layout('horizontal', [
            Tui::sidebar()->widget(
                Tui::list()->items(['Dashboard', 'Users', 'Settings'])
            ),
            $screen->layout('vertical', [
                Tui::block()->title('Main Content')->widget(
                    Tui::paragraph()->text('This is the main content area.')
                ),
                Tui::block()->title('Details')->widget(
                    Tui::paragraph()->text('More info here...')
                ),
            ])->grow(),
        ]),
    ]);
})->waitForInput();
```

## Features
- Alternate screen buffer for clean, non-scrolling TUIs
- Handles terminal resizing and input automatically
- Widgets: navbar, block, table, list, sidebar, paragraph, etc.
- Composable, deeply nestable layouts
- Designed for extensibility

## Status
**In early development.**
- Core layout engine and redraw loop in progress
- Widget and layout APIs subject to change

## Roadmap
See [TODO.md](TODO.md) for next steps and architectural notes.