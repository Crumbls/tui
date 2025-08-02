<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default TUI Theme
    |--------------------------------------------------------------------------
    |
    | This value controls the default theme used by TUI components.
    | You can customize the appearance of your terminal interface here.
    |
    */
    'theme' => 'default',

    /*
    |--------------------------------------------------------------------------
    | TUI Component Settings
    |--------------------------------------------------------------------------
    |
    | Configure default settings for TUI components like tables, forms,
    | progress bars, and other terminal interface elements.
    |
    */
    'components' => [
        'table' => [
            'border_style' => 'rounded',
            'show_header' => true,
        ],
        
        'progress_bar' => [
            'width' => 50,
            'fill_char' => '█',
            'empty_char' => '░',
        ],
    ],
];