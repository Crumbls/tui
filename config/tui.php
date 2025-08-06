<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TUI Contract Bindings
    |--------------------------------------------------------------------------
    |
    | This section allows you to override the default implementations of
    | TUI contracts. This is useful for testing or when you want to 
    | provide custom implementations of core TUI functionality.
    |
    */
    'bindings' => [
        'terminal' => \Crumbls\Tui\Terminal\Terminal::class,
        'renderer' => \Crumbls\Tui\Terminal\Renderer::class,
        'screen' => \Crumbls\Tui\Terminal\Screen::class,
        'focus_bus' => \Crumbls\Tui\Terminal\FocusBus::class,
        'input_bus' => \Crumbls\Tui\Terminal\InputBus::class,
        'hit_tester' => \Crumbls\Tui\Terminal\HitTester::class,
        'event_bus' => \Crumbls\Tui\Terminal\EventBus::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Terminal Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default terminal behavior, dimensions, and capabilities.
    |
    */
    'terminal' => [
        'width' => null, // null = auto-detect
        'height' => null, // null = auto-detect
        'raw_mode' => true,
        'alternate_screen' => true,
        'mouse_tracking' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme Configuration
    |--------------------------------------------------------------------------
    |
    | Control the visual appearance of TUI components including colors,
    | borders, and styling options.
    |
    */
    'theme' => [
        'name' => env('TUI_THEME', 'default'),
        
        'colors' => [
            'primary' => 'blue',
            'secondary' => 'gray',
            'success' => 'green',
            'warning' => 'yellow',
            'danger' => 'red',
            'info' => 'cyan',
        ],

        'borders' => [
            'default' => 'rounded',
            'focus_color' => 'blue',
            'styles' => [
                'none' => ['', '', '', '', '', '', '', ''],
                'simple' => ['─', '│', '┌', '┐', '└', '┘', '├', '┤'],
                'rounded' => ['─', '│', '╭', '╮', '╰', '╯', '├', '┤'],
                'double' => ['═', '║', '╔', '╗', '╚', '╝', '╠', '╣'],
                'thick' => ['━', '┃', '┏', '┓', '┗', '┛', '┣', '┫'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Key Binding Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default keyboard shortcuts and navigation behavior.
    | These can be overridden by individual components.
    |
    */
    'key_bindings' => [
        'quit' => 'q',
        'tab_next' => 'tab',
        'tab_previous' => 'shift+tab',
        'focus_next' => 'tab',
        'focus_previous' => 'shift+tab',
        
        // Reserved system controls (cannot be overridden by components)
        'reserved' => [
            'ctrl+c', 'ctrl+z', 'ctrl+d', 'ctrl+s', 'ctrl+q', 'ctrl+\\',
            'alt+f4', 'cmd+q', 'ctrl+alt+del',
            'ctrl+a', 'ctrl+x', 'ctrl+v', 'ctrl+n', 'ctrl+o', 'ctrl+p',
            'ctrl+f', 'ctrl+h', 'ctrl+r', 'ctrl+t', 'ctrl+w', 'ctrl+y',
            'ctrl+u', 'ctrl+i', 'ctrl+k', 'ctrl+l', 'ctrl+e', 'ctrl+b',
            'ctrl+m', 'ctrl+j', 'tab', 'shift+tab', 'enter', 'escape', 'space',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Component Configuration
    |--------------------------------------------------------------------------
    |
    | Default settings for various TUI components.
    |
    */
    'components' => [
        'layout' => [
            'auto_size' => true,
            'reserve_prompt_line' => true,
        ],

        'panel' => [
            'default_border' => true,
            'padding' => 1,
        ],

        'tabs' => [
            'marker_active' => '► ',
            'marker_inactive' => '  ',
            'separator' => ' | ',
        ],

        'block' => [
            'default_width' => 20,
            'default_height' => 10,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Focus Management
    |--------------------------------------------------------------------------
    |
    | Configure focus behavior and navigation settings.
    |
    */
    'focus' => [
        'enabled' => true,
        'wrap_around' => true,
        'auto_focus_first' => true,
        'visual_indicators' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Event System Configuration
    |--------------------------------------------------------------------------
    |
    | Configure event handling and propagation behavior.
    |
    */
    'events' => [
        'bubbling_enabled' => true,
        'stop_on_handled' => true,
        'mouse_events' => true,
        'keyboard_events' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching and performance optimization settings.
    |
    */
    'performance' => [
        'cache_layouts' => false,
        'cache_renders' => false,
        'debounce_resize' => 100, // milliseconds
        'buffer_updates' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Development & Debugging
    |--------------------------------------------------------------------------
    |
    | Settings useful during development and debugging.
    |
    */
    'debug' => [
        'enabled' => env('APP_DEBUG', false),
        'log_events' => false,
        'log_focus_changes' => false,
        'log_key_bindings' => false,
        'show_component_bounds' => false,
    ],
];