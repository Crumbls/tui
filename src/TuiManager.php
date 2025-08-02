<?php

declare(strict_types=1);

namespace Crumbls\Tui;

use Crumbls\Tui\Contracts\TerminalInterface;

class TuiManager
{
    public function __construct(
        protected TerminalInterface $terminal
    ) {
    }

    public function builder(?TerminalInterface $terminal = null): TuiBuilder
    {
        return TuiBuilder::make($terminal ?? $this->terminal);
    }

    public function widget(): Widget
    {
        return new class extends Widget {
            public function render(): string
            {
                return '';
            }
        };
    }

    public function block(): Widgets\Block
    {
        return Widget::block();
    }

    public function table(): Widgets\Table
    {
        return Widget::table();
    }

    public function chart(): Widgets\Chart
    {
        return Widget::chart();
    }

    public function gauge(): Widgets\Gauge
    {
        return Widget::gauge();
    }

    public function grid(): Widgets\Grid
    {
        return Widget::grid();
    }

    public function list(): Widgets\ListWidget
    {
        return Widget::list();
    }

    public function paragraph(): Widgets\Paragraph
    {
        return Widget::paragraph();
    }

    public function sparkline(): Widgets\Sparkline
    {
        return Widget::sparkline();
    }

    public function canvas(): Widgets\Canvas
    {
        return Widget::canvas();
    }

    public function tabs(): Widgets\Tabs
    {
        return Widget::tabs();
    }

    public function navbar(): Widgets\Navbar
    {
        return Widget::navbar();
    }

    public function textInput(): Widgets\TextInput
    {
        return Widget::textInput();
    }

    public function select(): Widgets\Select
    {
        return Widget::select();
    }

    public function button(): Widgets\Button
    {
        return Widget::button();
    }

    public function form(): Widgets\Form
    {
        return Widget::form();
    }
}