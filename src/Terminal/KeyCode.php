<?php

declare(strict_types=1);

namespace Crumbls\Tui\Terminal;

/**
 * Key codes for special keys - matches PhpTui's KeyCode
 */
enum KeyCode: string
{
    case Backspace = 'backspace';
    case Enter = 'enter';
    case Left = 'left';
    case Right = 'right';
    case Up = 'up';
    case Down = 'down';
    case Home = 'home';
    case End = 'end';
    case PageUp = 'page_up';
    case PageDown = 'page_down';
    case Tab = 'tab';
    case BackTab = 'back_tab';
    case Delete = 'delete';
    case Insert = 'insert';
    case F1 = 'f1';
    case F2 = 'f2';
    case F3 = 'f3';
    case F4 = 'f4';
    case F5 = 'f5';
    case F6 = 'f6';
    case F7 = 'f7';
    case F8 = 'f8';
    case F9 = 'f9';
    case F10 = 'f10';
    case F11 = 'f11';
    case F12 = 'f12';
    case Esc = 'esc';
}