<?php

declare(strict_types=1);

namespace Crumbls\Tui\Facades;

use Crumbls\Tui\Contracts\TerminalInterface;
use Crumbls\Tui\TuiBuilder;
use Crumbls\Tui\Widget;
use Illuminate\Support\Facades\Facade;

/**
 * @method static TuiBuilder builder(?TerminalInterface $terminal = null)
 * @method static Widget widget()
 * @method static \Crumbls\Tui\Widgets\Block block()
 * @method static \Crumbls\Tui\Widgets\Table table()
 * @method static \Crumbls\Tui\Widgets\Chart chart()
 * @method static \Crumbls\Tui\Widgets\Gauge gauge()
 * @method static \Crumbls\Tui\Widgets\Grid grid()
 * @method static \Crumbls\Tui\Widgets\ListWidget list()
 * @method static \Crumbls\Tui\Widgets\Paragraph paragraph()
 * @method static \Crumbls\Tui\Widgets\Sparkline sparkline()
 * @method static \Crumbls\Tui\Widgets\Canvas canvas()
 * @method static \Crumbls\Tui\Widgets\Tabs tabs()
 * @method static \Crumbls\Tui\Widgets\TextInput textInput()
 * @method static \Crumbls\Tui\Widgets\Select select()
 * @method static \Crumbls\Tui\Widgets\Button button()
 * @method static \Crumbls\Tui\Widgets\Form form()
 */
class Tui extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'tui';
    }
}