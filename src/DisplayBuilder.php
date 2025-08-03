<?php

declare(strict_types=1);

namespace Crumbls\Tui;

use Crumbls\Tui\Bridge\PhpTerm\PhpTermBackend;
use Crumbls\Tui\Canvas\AggregateShapePainter;
use Crumbls\Tui\Canvas\ShapePainter;
use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Backend;
use Crumbls\Tui\Display\Display;
use Crumbls\Tui\Display\DisplayExtension;
use Crumbls\Tui\Display\DisplayWithTerminal;
use Crumbls\Tui\Display\Viewport;
use Crumbls\Tui\Display\Viewport\Fixed;
use Crumbls\Tui\Display\Viewport\Fullscreen;
use Crumbls\Tui\Display\Viewport\Inline;
use Crumbls\Tui\Extension\Core\CoreExtension;
use Crumbls\Tui\Extension\Core\Widget\CanvasRenderer;
use Crumbls\Tui\Terminal\Terminal;
use Crumbls\Tui\Widget\WidgetRenderer;
use Crumbls\Tui\Widget\WidgetRenderer\AggregateWidgetRenderer;

/**
 * An entry point for PHP-TUI.
 *
 * You can use this class to get the Display object
 * upon which you can start rendering widgets.
 *
 * ```
 * $display = DisplayBuilder::default()->build();
 * $display->draw(
 *    Paragraph::fromString("Hello World")
 * );
 * ```
 *
 * By default it will use the PhpTermBackend in fullscreen mode.
 */
final class DisplayBuilder
{
    /**
     * @var ShapePainter[]
     */
    private array $shapePainters = [];

    /**
     * @var WidgetRenderer[]
     */
    private array $widgetRenderers = [];
    
    /**
     * @param DisplayExtension[] $extensions
     */
    private function __construct(
        private readonly Backend $backend,
        private ?Viewport $viewport,
        private array $extensions,
        private ?Terminal $terminal = null
    ) {
    }

    /**
     * Return a new display with no extensions.
     *
     * @param DisplayExtension[] $extensions
     */
    public static function new(?Backend $backend, array $extensions = []): self
    {
        return new self(
            $backend ?? PhpTermBackend::new(),
            null,
            $extensions,
            null
        );
    }
    
    /**
     * Return a default display with the core extension and auto-created terminal.
     */
    public static function default(?Backend $backend = null): self
    {
        $terminal = Terminal::new();
        $terminal->setupForTui();
        
        return new self(
            $backend ?? PhpTermBackend::new(),
            null,
            [new CoreExtension()],
            $terminal
        );
    }

    /**
     * Create a new display builder using our fluent Terminal
     */
    public static function fromTerminal(Terminal $terminal, array $extensions = []): self
    {
        $terminal->setupForTui();
        return new self(
            PhpTermBackend::new(),
            null,
            $extensions,
            $terminal
        );
    }

    /**
     * Create a default display builder using our fluent Terminal
     */
    public static function defaultFromTerminal(Terminal $terminal): self
    {
        $terminal->setupForTui();
        return self::fromTerminal($terminal, [
            new CoreExtension(),
        ]);
    }

    /**
     * Explicitly require a fullscreen viewport
     */
    public function fullscreen(): self
    {
        $this->viewport = new Fullscreen();

        return $this;
    }

    /**
     * When set the display will be of the specified height _after_ the row
     * that the cursor is on.
     * @param int<0,max> $height
     */
    public function inline(int $height): self
    {
        $this->viewport = new Inline($height);

        return $this;
    }

    /**
     * When set the display will be at the specified (x,y) position with the
     * specified width and height.
     * @param positive-int $x
     * @param positive-int $y
     * @param positive-int $width
     * @param positive-int $height
     */
    public function fixed(int $x, int $y, int $width, int $height): self
    {
        $this->viewport = new Fixed(Area::fromScalars($x, $y, $width, $height));

        return $this;
    }

    /**
     * Build and return the Display.
     */
    public function build(): DisplayWithTerminal
    {
        foreach ($this->extensions as $extension) {
            foreach ($extension->shapePainters() as $shapePainter) {
                $this->shapePainters[] = $shapePainter;
            }
            foreach ($extension->widgetRenderers() as $widgetRenderers) {
                $this->widgetRenderers[] = $widgetRenderers;
            }
        }

        $display = Display::new(
            $this->backend,
            $this->viewport ?? new Fullscreen(),
            new AggregateWidgetRenderer([
                ...$this->shapePainters ? [$this->buildCanvasRenderer()] : [],
                ...$this->widgetRenderers,
            ])
        );

        return new DisplayWithTerminal($display, $this->terminal);
    }

    /**
     * Add a shape painter.
     *
     * When at least one shape painter is added the Canvas widget will
     * automatically be enabled.
     */
    public function addShapePainter(ShapePainter $shapePainter): self
    {
        $this->shapePainters[] = $shapePainter;

        return $this;
    }

    /**
     * Add a widget renderer
     */
    public function addWidgetRenderer(WidgetRenderer $widgetRenderer): self
    {
        $this->widgetRenderers[] = $widgetRenderer;

        return $this;
    }

    /**
     * Add a display extension.
     */
    public function addExtension(DisplayExtension $extension): self
    {
        $this->extensions[] = $extension;

        return $this;
    }

    /**
     * Get the terminal instance
     */
    public function getTerminal(): ?Terminal
    {
        return $this->terminal;
    }

    /**
     * Set the terminal instance
     */
    public function setTerminal(Terminal $terminal): self
    {
        $this->terminal = $terminal;
        return $this;
    }

    private function buildCanvasRenderer(): WidgetRenderer
    {
        return new CanvasRenderer(new AggregateShapePainter($this->shapePainters));
    }
}
