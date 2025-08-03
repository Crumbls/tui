<?php

declare(strict_types=1);

namespace Crumbls\Tui\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Crumbls\Tui\Display\Backend;
use Crumbls\Tui\Bridge\PhpTerm\PhpTermBackend;
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Widget\BlockWidget;
use Crumbls\Tui\Components\Block;
use Crumbls\Tui\Extension\Core\Widget\GridWidget;
use Crumbls\Tui\Extension\Core\Widget\ParagraphWidget;
use Crumbls\Tui\Extension\Core\Widget\TabsWidget;
use Crumbls\Tui\Extension\Core\Widget\ListWidget;
use Crumbls\Tui\Extension\Core\Widget\List\ListItem;
use Crumbls\Tui\Extension\Core\Widget\List\ListState;
use Crumbls\Tui\Extension\Core\Widget\GaugeWidget;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;
use Crumbls\Tui\Extension\Core\Widget\TableWidget;
use Crumbls\Tui\Extension\Core\Widget\Table\TableRow;
use Crumbls\Tui\Extension\Core\Widget\Table\TableCell;
use Crumbls\Tui\Extension\Core\Widget\Table\TableState;
use Crumbls\Tui\Extension\Core\Shape\CircleShape;
use Crumbls\Tui\Extension\Core\Shape\RectangleShape;
use Crumbls\Tui\Extension\Core\Shape\LineShape;
use Crumbls\Tui\Extension\Core\Shape\PointsShape;
use Crumbls\Tui\Extension\Core\Shape\SpriteShape;
use Crumbls\Tui\Color\RgbColor;
use Crumbls\Tui\Canvas\CanvasContext;
use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Position\FloatPosition;
use Crumbls\Tui\Layout\Constraint;
use Crumbls\Tui\Widget\Borders;
use Crumbls\Tui\Widget\Direction;
use Crumbls\Tui\Text\Line;
use Crumbls\Tui\Text\Title;
use Crumbls\Tui\Text\Text;
use Crumbls\Tui\Text\Span;
use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\Terminal\Terminal;
use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\Event\CodedKeyEvent;
use PhpTui\Term\Event\MouseEvent;
use PhpTui\Term\KeyCode;
use PhpTui\Term\KeyModifiers;

class TuiDemoCommand extends Command
{
    protected $signature = 'tui:demo';
    protected $description = 'Modern TUI demo showcasing the layout system and components';

    private int $currentTab = 0;
    private array $tabs = ['Welcome', 'Components', 'Canvas', 'Images', 'Lists', 'Progress', 'Tables'];
    private array $events = [];
    private ListState $listState;
    private array $downloads = [];
    private int $animationFrame = 0;
    private array $cachedLogos = [];
    private array $logoSprites = [];

    private const SAMPLE_EVENTS = [
        ['Event1', 'INFO'],
        ['Event2', 'INFO'],
        ['Event3', 'CRITICAL'],
        ['Event4', 'ERROR'],
        ['Event5', 'INFO'],
        ['Event6', 'INFO'],
        ['Event7', 'WARNING'],
        ['Event8', 'INFO'],
        ['Event9', 'INFO'],
        ['Event10', 'INFO'],
        ['Event11', 'CRITICAL'],
        ['Event12', 'INFO'],
        ['Event13', 'INFO'],
        ['Event14', 'INFO'],
        ['Event15', 'INFO'],
        ['Event16', 'INFO'],
        ['Event17', 'ERROR'],
        ['Event18', 'ERROR'],
        ['Event19', 'INFO'],
        ['Event20', 'INFO'],
        ['Event21', 'WARNING'],
        ['Event22', 'INFO'],
        ['Event23', 'INFO'],
        ['Event24', 'WARNING'],
        ['Event25', 'INFO'],
        ['Event26', 'INFO'],
    ];

    public function handle()
    {
        $this->info('Starting TUI Demo...');
        
        // Initialize state
        $this->listState = new ListState(0, 0);
        
        try {
            $display = DisplayBuilder::default()->build();
            $terminal = $display->getTerminal();

            $rawModeEnabled = true;

            // Render once to show the interface
            $display->draw($this->buildLayout());

            
            if ($rawModeEnabled) {
                // Main event loop
                while (true) {
                    // Handle events

                    while (null !== $event = $terminal->events()->next()) {
                        if ($event instanceof CharKeyEvent) {
                            if ($event->modifiers === KeyModifiers::NONE) {
                                if ($event->char === 'q') {
                                    break 2;
                                }
                                if ($event->char >= '1' && $event->char <= '7') {
                                    $this->currentTab = intval($event->char) - 1;
                                    $display->draw($this->buildLayout());
                                }
                            }
                        }
                        if ($event instanceof CodedKeyEvent) {
                            if ($event->code === KeyCode::Tab) {
                                $this->currentTab = ($this->currentTab + 1) % count($this->tabs);
                                $display->draw($this->buildLayout());
                            }
                            if ($event->code === KeyCode::BackTab) {
                                $this->currentTab = ($this->currentTab - 1 + count($this->tabs)) % count($this->tabs);
                                $display->draw($this->buildLayout());
                            }
                        }
                        
                        // Log event
                        array_unshift($this->events, $event->__toString());
                        if (count($this->events) > 10) {
                            array_pop($this->events);
                        }
                    }

                    // Re-render for animation on canvas, images, progress, and tables tabs
                    if ($this->currentTab === 2 || $this->currentTab === 3 || $this->currentTab === 5 || $this->currentTab === 6) {
	                    $this->animationFrame++;
	                    $display->draw($this->buildLayout());
                    }

                    usleep(50_000);
                }
            } else {
                // Static display mode - show animated canvas demo
                $this->info('Displaying animated TUI demo (raw mode not available)...');
                $this->info('Showing Canvas animation for 15 seconds...');
                
                // Start with canvas tab for animation demo
                $this->currentTab = 1; // Canvas tab
                $startTime = time();
                $frame = 0;
                
                while ((time() - $startTime) < 15) { // Run for 15 seconds
                    $this->animationFrame = $frame;
                    $display->draw($this->buildLayout());
                    usleep(100_000); // 100ms per frame (10 FPS)
                    $frame++;
                }
                
                $this->info('Canvas animation completed! Canvas shows:');
                $this->info('- Moving red circle orbiting in complex patterns');
                $this->info('- Rotating green line sweeping like radar');
                $this->info('- Pulsing blue rectangle growing/shrinking');
                $this->info('- Yellow star field scrolling across screen');
                $this->info('- Cyan sine wave flowing continuously');
                $this->info('');
                $this->info('For interactive experience with all tabs, run in proper terminal with raw mode support.');
            }
        } catch (\Exception $e) {
            $this->error('TUI Demo failed: ' . $e->getMessage());
        }
        // Terminal cleanup happens automatically via __destruct

        $this->info('TUI Demo finished.');
        return 0;
    }

    private function buildLayout()
    {
        return GridWidget::default()
            ->direction(Direction::Vertical)
            ->constraints(
                Constraint::min(3),
                Constraint::min(1),
            )
            ->widgets(
                $this->buildHeader(),
                $this->buildContent(),
            );
    }

    private function buildHeader()
    {
        return BlockWidget::default()
            ->borders(Borders::ALL)
            ->style(Style::default()->white())
            ->widget(
                TabsWidget::fromTitles(
                    Line::parse('<fg=red>Cmd + Q</> to quit'),
                    Line::fromString('Welcome'),
                    Line::fromString('Components'),
                    Line::fromString('Canvas'),
                    Line::fromString('Images'),
                    Line::fromString('Lists'),
                    Line::fromString('Progress'),
                    Line::fromString('Tables'),
                )->select($this->currentTab + 1)
                ->highlightStyle(Style::default()->white()->onBlue())
            );
    }

    private function buildContent()
    {
		if ($this->currentTab) {
		}
        return match ($this->currentTab) {
			0 => $this->buildWelcomePage(),
			1 => $this->buildComponentsPage(),
	        2 => $this->buildCanvasPage(),
	        3 => $this->buildImagesPage(),
	        4 => $this->buildListsPage(),
	        5 => $this->buildProgressPage(),
	        6 => $this->buildTablesPage(),
            default => $this->buildWelcomePage(),
        };
    }

    private function buildWelcomePage()
    {
        return GridWidget::default()
            ->constraints(
                Constraint::min(5),
                Constraint::min(5),
            )
            ->widgets(
                BlockWidget::default()
                    ->titles(Title::fromString('Welcome to PHP TUI Demo!'))
                    ->borders(Borders::ALL)
                    ->widget(
                        ParagraphWidget::fromLines(
                            Line::fromString('This is a TUI demo built with the Crumbls TUI package.'),
                            Line::fromString(''),
                            Line::fromString('🎯 Controls:'),
                            Line::fromString('- Press 1-5 to switch tabs'),
                            Line::fromString('- Use Tab/Shift+Tab to navigate'),
                            Line::fromString('- Press q to quit'),
                            Line::fromString(''),
                            Line::fromString('📋 Featured demos:'),
                            Line::fromString('- Tab 2: ⚡ Modern Components (NEW!)'),
                            Line::fromString('- Tab 3: 🎨 Canvas with animated shapes'),
                            Line::fromString('- Tab 4: 🖼️ Image rendering'),
                            Line::fromString('- Tab 5: 📋 Lists and data'),
                            Line::fromString('- Tab 6: 🚀 Animated progress bars!'),
                            Line::fromString('- Tab 7: 📊 Tables and data display'),
                        )
                    ),
                BlockWidget::default()
                    ->titles(Title::fromString('Recent Events'))
                    ->borders(Borders::ALL)
                    ->widget(
                        ParagraphWidget::fromLines(
                            ...array_map(fn($event) => Line::fromString($event), $this->events)
                        )
                    )
            );
    }

    private function buildComponentsPage()
    {
        return GridWidget::default()
            ->direction(Direction::Vertical)
            ->constraints(
                Constraint::length(6),
                Constraint::min(1),
            )
            ->widgets(
                // Header with info
                BlockWidget::default()
                    ->titles(Title::fromString('⚡ Modern Component System'))
                    ->borders(Borders::ALL)
                    ->style(Style::default()->fg(AnsiColor::Cyan))
                    ->widget(
                        ParagraphWidget::fromLines(
                            Line::fromString('Showcasing our new component architecture with event handling'),
                            Line::fromString('Components: DOM-like event bubbling, focus management, fluent API'),
                            Line::fromString('Old vs New: Compare BlockWidget (legacy) vs Block (modern)'),
                            Line::fromString('Event System: Click handling, keyboard shortcuts, focus states'),
                        )
                    ),
                
                // Demo grid showing different component styles
                GridWidget::default()
                    ->direction(Direction::Horizontal)
                    ->constraints(
                        Constraint::percentage(33),
                        Constraint::percentage(33),
                        Constraint::percentage(34),
                    )
                    ->widgets(
                        // Legacy BlockWidget example
                        BlockWidget::default()
                            ->titles(Title::fromString('Legacy BlockWidget'))
                            ->borders(Borders::ALL)
                            ->borderType(\Crumbls\Tui\Widget\BorderType::Plain)
                            ->style(Style::default()->fg(AnsiColor::Gray))
                            ->widget(
                                ParagraphWidget::fromLines(
                                    Line::fromString('Old way:'),
                                    Line::fromString(''),
                                    Line::fromString('BlockWidget::default()'),
                                    Line::fromString('  ->borders(Borders::ALL)'),
                                    Line::fromString('  ->titles(...)'),
                                    Line::fromString('  ->widget(content)'),
                                    Line::fromString(''),
                                    Line::fromString('No event handling'),
                                    Line::fromString('No focus management'),
                                    Line::fromString('Verbose setup'),
                                )
                            ),
                        
                        // Modern Block component - we'll need to convert this to work with existing renderer
                        BlockWidget::default()
                            ->titles(Title::fromString('Modern Block (Concept)'))
                            ->borders(Borders::ALL)
                            ->borderType(\Crumbls\Tui\Widget\BorderType::Rounded)
                            ->style(Style::default()->fg(AnsiColor::Green))
                            ->widget(
                                ParagraphWidget::fromLines(
                                    Line::fromString('New way:'),
                                    Line::fromString(''),
                                    Line::fromString('Block::card()'),
                                    Line::fromString('  ->title("Hello")'),
                                    Line::fromString('  ->focusable()'),
                                    Line::fromString('  ->onClick($handler)'),
                                    Line::fromString('  ->content($child)'),
                                    Line::fromString(''),
                                    Line::fromString('DOM-like events'),
                                    Line::fromString('Focus management'),
                                    Line::fromString('Fluent API'),
                                )
                            ),
                        
                        // Event demo
                        BlockWidget::default()
                            ->titles(Title::fromString('Event System'))
                            ->borders(Borders::ALL)
                            ->borderType(\Crumbls\Tui\Widget\BorderType::Double)
                            ->style(Style::default()->fg(AnsiColor::Yellow))
                            ->widget(
                                ParagraphWidget::fromLines(
                                    Line::fromString('Event Features:'),
                                    Line::fromString(''),
                                    Line::fromString('• KeyPress: ctrl+a'),
                                    Line::fromString('• Click: coordinates'),
                                    Line::fromString('• Focus/Blur states'),
                                    Line::fromString('• Event bubbling'),
                                    Line::fromString(''),
                                    Line::fromString('$component'),
                                    Line::fromString('  ->onKeyPress($fn)'),
                                    Line::fromString('  ->onClick($fn)'),
                                    Line::fromString('  ->focusable()'),
                                )
                            ),
                    )
            );
    }

    private function buildCanvasPage()
    {
        // Update animation frame for canvas
        $time = $this->animationFrame / 10.0;
        
        // Full-height canvas without info panel for maximum display area
        return CanvasWidget::fromIntBounds(0, 120, 0, 60)
            ->marker(Marker::Braille)
            ->backgroundColor(AnsiColor::Black)
                            ->paint(function (CanvasContext $context) use ($time): void {
                                // Initialize logo sprites if needed
                                $this->initializeLogoSprites();
                                
                                // Update and render logo sprites
                                $this->updateAndRenderLogoSprites($context, $time);
                                
                                // ASCII ART SPRITE DEMO - Like php-tui examples
                                
                                // Moving PHP Elephant ASCII art sprite
                                $elephantX = 5 + ($this->animationFrame % 60);
                                $elephantSprite = new SpriteShape(
                                    rows: [
                                        '     __     ',
                                        '    /  \\   ',
                                        '   ( __  \\  ',
                                        '  (   \\   ) ',
                                        '   \\   | |  ',
                                        '    \\  | |  ',
                                        '     ) | |  ',
                                        '    /  | |  ',
                                        '   |   | |  ',
                                        '    \\_____|  ',
                                    ],
                                    color: AnsiColor::Blue,
                                    position: FloatPosition::at($elephantX, 5),
                                    alphaChar: ' '
                                );
                                $context->draw($elephantSprite);
                                
                                // Animated face sprite that changes expression
                                $faceFrame = ($this->animationFrame / 10) % 3;
                                $faceSprites = [
                                    [' o   o ', '   ^   ', ' \\_-_/ '], // happy
                                    [' -   - ', '   ^   ', ' \\___/ '], // neutral  
                                    [' x   x ', '   v   ', ' /---\\ '], // dizzy
                                ];
                                
                                $faceSprite = new SpriteShape(
                                    rows: $faceSprites[$faceFrame],
                                    color: AnsiColor::Yellow,
                                    position: FloatPosition::at(20, 20),
                                    alphaChar: ' '
                                );
                                $context->draw($faceSprite);
                                
                                // Scrolling spaceship
                                $shipX = ($this->animationFrame * 2) % 90;
                                $shipSprite = new SpriteShape(
                                    rows: [
                                        '    /\\    ',
                                        '   /  \\   ',
                                        '  |    |  ',
                                        '  |::::|  ',
                                        '   \\__/   ',
                                        '    ||    ',
                                        '   ~~~~   ',
                                    ],
                                    color: AnsiColor::Cyan,
                                    position: FloatPosition::at($shipX, 30),
                                    alphaChar: ' '
                                );
                                $context->draw($shipSprite);
                                
                                // Bouncing logo sprite
                                $logoY = 35 + abs(sin($time * 0.8) * 8);
                                $logoSprite = new SpriteShape(
                                    rows: [
                                        '██╗      █████╗ ██████╗ ',
                                        '██║     ██╔══██╗██╔══██╗',
                                        '██║     ███████║██████╔╝',
                                        '██║     ██╔══██║██╔══██╗',
                                        '███████╗██║  ██║██║  ██║',
                                        '╚══════╝╚═╝  ╚═╝╚═╝  ╚═╝',
                                    ],
                                    color: AnsiColor::Red,
                                    position: FloatPosition::at(10, $logoY),
                                    alphaChar: ' '
                                );
                                $context->draw($logoSprite);
                                
                                // Walking character with animation frames
                                $walkX = 40 + ($this->animationFrame % 50);
                                $walkFrame = ($this->animationFrame / 3) % 4;
                                $walkSprites = [
                                    ['  o  ', ' /|\\ ', ' / \\ '], // frame 1
                                    ['  o  ', ' /|\\ ', '/   \\'], // frame 2
                                    ['  o  ', ' /|\\ ', ' | | '], // frame 3
                                    ['  o  ', ' /|\\ ', '\\   /'], // frame 4
                                ];
                                
                                $walkSprite = new SpriteShape(
                                    rows: $walkSprites[$walkFrame],
                                    color: AnsiColor::Green,
                                    position: FloatPosition::at($walkX, 15),
                                    alphaChar: ' '
                                );
                                $context->draw($walkSprite);
                                
                                // Rotating ASCII spinner
                                $spinFrame = ($this->animationFrame / 2) % 4;
                                $spinChars = ['|', '/', '-', '\\'];
                                $spinSprite = new SpriteShape(
                                    rows: [$spinChars[$spinFrame]],
                                    color: AnsiColor::White,
                                    position: FloatPosition::at(70, 25),
                                    alphaChar: ' '
                                );
                                $context->draw($spinSprite);
                            });
    }

    private function buildImagesPage()
    {
        return BlockWidget::default()
            ->titles(Title::fromString('Image Demo - Converted to Terminal Graphics'))
            ->borders(Borders::ALL)
            ->widget(
                GridWidget::default()
                    ->constraints(
                        Constraint::length(3), // Smaller info panel
                        Constraint::min(1), // Main canvas takes full remaining height
                    )
                    ->widgets(
                        // Info panel
                        BlockWidget::default()
                            ->borders(Borders::ALL)
                            ->style(Style::default()->fg(AnsiColor::Cyan))
                            ->widget(
                                ParagraphWidget::fromLines(
                                    Line::fromString('Custom Image to Terminal Graphics Conversion'),
                                )
                            ),
                        // Main canvas with centered Laravel logo (full height)
                        CanvasWidget::fromIntBounds(0, 120, 0, 60)
                            ->marker(Marker::Braille)
                            ->backgroundColor(AnsiColor::Black)
                            ->paint(function (CanvasContext $context): void {
                                // Laravel Logo centered with dynamic sizing
                                $laravelImage = $this->createLaravelLogoPixelArt();
                                
                                // Get actual image dimensions
                                $imageHeight = count($laravelImage);
                                $imageWidth = $imageHeight > 0 ? count($laravelImage[0]) : 0;
                                
                                // Calculate center position dynamically (120x60 canvas)
                                $centerX = (120 - $imageWidth) / 2;
                                $centerY = (60 - $imageHeight) / 2;
                                
                                $this->renderPixelArtToCanvas($context, $laravelImage, $centerX, $centerY);
                            })
                    )
            );
    }

    private function buildListsPage()
    {
        return BlockWidget::default()
            ->titles(Title::fromString('Lists Demo'))
            ->borders(Borders::ALL)
            ->widget(
                ListWidget::default()
                    ->state($this->listState)
                    ->highlightSymbol('>> ')
                    ->highlightStyle(Style::default()->fg(AnsiColor::Yellow))
                    ->items(...array_map(function (array $event) {
                        return ListItem::new(Text::fromLine(Line::fromSpans(
                            Span::fromString($event[1])->fg(match ($event[1]) {
                                'INFO' => AnsiColor::Green,
                                'WARNING' => AnsiColor::Yellow,
                                'CRITICAL' => AnsiColor::Red,
                                'ERROR' => AnsiColor::LightRed,
                                default => AnsiColor::Cyan,
                            }),
                            Span::fromString(' '),
                            Span::fromString($event[0]),
                        )));
                    }, array_merge(self::SAMPLE_EVENTS, self::SAMPLE_EVENTS)))
            );
    }

    private function buildTablesPage()
    {
        return BlockWidget::default()
            ->titles(Title::fromString('📊 Tables Demo - Data Display'))
            ->borders(Borders::ALL)
            ->widget(
                GridWidget::default()
                    ->constraints(
                        Constraint::length(4), // Info panel
                        Constraint::length(12), // User table
                        Constraint::min(1), // System stats table
                    )
                    ->widgets(
                        // Info panel
                        BlockWidget::default()
                            ->borders(Borders::ALL)
                            ->style(Style::default()->fg(AnsiColor::Cyan))
                            ->widget(
                                ParagraphWidget::fromLines(
                                    Line::fromString('📊 Table Widget Demonstrations'),
                                    Line::fromString('Various table layouts with headers, selection, and styling'),
                                    Line::fromString('Frame: ' . $this->animationFrame),
                                )
                            ),
                        
                        // User data table
                        BlockWidget::default()
                            ->titles(Title::fromString('👥 Users Table'))
                            ->borders(Borders::ALL)
                            ->widget(
                                TableWidget::default()
                                    ->widths(
                                        Constraint::length(4),     // ID
                                        Constraint::min(15),       // Name  
                                        Constraint::length(20),    // Email
                                        Constraint::length(10),    // Role
                                        Constraint::length(8),     // Status
                                    )
                                    ->header(
                                        TableRow::fromStrings('ID', 'Name', 'Email', 'Role', 'Status')
                                    )
                                    ->rows(
                                        TableRow::fromStrings('001', 'Alice Johnson', 'alice@example.com', 'Admin', 'Active'),
                                        TableRow::fromStrings('002', 'Bob Smith', 'bob@company.org', 'Developer', 'Active'),
                                        TableRow::fromStrings('003', 'Carol Davis', 'carol.d@startup.io', 'Designer', 'Away'),
                                        TableRow::fromStrings('004', 'David Wilson', 'david@freelance.com', 'Consultant', 'Offline'),
                                        TableRow::fromStrings('005', 'Eve Brown', 'eve.brown@tech.co', 'Manager', 'Active'),
                                        TableRow::fromStrings('006', 'Frank Miller', 'frank@devstudio.net', 'Developer', 'Active'),
                                        TableRow::fromStrings('007', 'Grace Lee', 'grace@design.com', 'Designer', 'Active'),
                                    )
                                    ->highlightSymbol('→ ')
                                    ->highlightStyle(Style::default()->bg(AnsiColor::Blue)->fg(AnsiColor::White))
                                    ->select(($this->animationFrame / 20) % 7) // Animate selection
                            ),
                        
                        // System stats table
                        BlockWidget::default()
                            ->titles(Title::fromString('⚡ System Statistics'))
                            ->borders(Borders::ALL)
                            ->widget(
                                TableWidget::default()
                                    ->widths(
                                        Constraint::length(18),    // Metric
                                        Constraint::length(12),    // Current
                                        Constraint::length(12),    // Average
                                        Constraint::min(10),       // Status
                                    )
                                    ->header(
                                        TableRow::fromStrings('Metric', 'Current', 'Average', 'Status')
                                    )
                                    ->rows(
                                        ...$this->generateSystemStatsRows()
                                    )
                                    ->highlightSymbol('▶ ')
                                    ->highlightStyle(Style::default()->bg(AnsiColor::Green)->fg(AnsiColor::Black))
                            ),
                    )
            );
    }

    /**
     * Generate animated system statistics rows
     */
    private function generateSystemStatsRows(): array
    {
        $time = $this->animationFrame;
        
        // Generate realistic-looking animated data
        $cpuUsage = 45 + (int)(15 * sin($time * 0.1));
        $ramUsage = 62 + (int)(8 * sin($time * 0.08));
        $diskUsage = 78 + (int)(5 * sin($time * 0.05));
        $networkIn = 120 + (int)(80 * sin($time * 0.12));
        $networkOut = 85 + (int)(40 * sin($time * 0.15));
        $processes = 156 + (int)(20 * sin($time * 0.06));
        
        return [
            TableRow::fromStrings('CPU Usage', $cpuUsage . '%', '52%', $cpuUsage > 60 ? '🔴 High' : '🟢 Normal'),
            TableRow::fromStrings('RAM Usage', $ramUsage . '%', '58%', $ramUsage > 70 ? '🟡 Warn' : '🟢 OK'),
            TableRow::fromStrings('Disk Usage', $diskUsage . '%', '75%', $diskUsage > 85 ? '🔴 Full' : '🟢 OK'),
            TableRow::fromStrings('Network In', $networkIn . ' MB/s', '95 MB/s', '🟢 Normal'),
            TableRow::fromStrings('Network Out', $networkOut . ' MB/s', '67 MB/s', '🟢 Normal'),
            TableRow::fromStrings('Processes', (string)$processes, '142', $processes > 170 ? '🟡 Many' : '🟢 OK'),
            TableRow::fromStrings('Uptime', '5d 12h 34m', '3d 8h', '🟢 Stable'),
            TableRow::fromStrings('Load Average', '1.2, 1.5, 1.3', '1.4, 1.6, 1.2', '🟢 Good'),
        ];
    }

    private function buildProgressPage()
    {
        // Update animation frame
        $this->animationFrame++;
        
        // Initialize downloads if empty
        if (empty($this->downloads)) {
            $this->downloads = [
//                ['name' => 'ubuntu-22.04.iso', 'size' => 4200, 'downloaded' => 0, 'speed' => 15],
                ['name' => 'job-queue-worker', 'size' => 850, 'downloaded' => 0, 'speed' => 25],
                ['name' => 'laravel-app.zip', 'size' => 320, 'downloaded' => 0, 'speed' => 45],
//                ['name' => 'docker-image.tar', 'size' => 1200, 'downloaded' => 0, 'speed' => 30],
                   ['name' => 'backup-data.sql', 'size' => 2500, 'downloaded' => 0, 'speed' => 20],
            ];
        }

        // Animate downloads
        foreach ($this->downloads as $index => &$download) {
            if ($download['downloaded'] < $download['size']) {
                $download['downloaded'] += $download['speed'] + rand(-5, 10);
                $download['downloaded'] = min($download['downloaded'], $download['size']);
            } else {
                // Reset completed downloads with some delay
                if ($this->animationFrame % 50 === 0) {
                    $download['downloaded'] = 0;
                }
            }
        }

        return BlockWidget::default()
            ->titles(Title::fromString('🚀 Live Progress Demo'))
            ->borders(Borders::ALL)
            ->widget(
                GridWidget::default()
                    ->constraints(
                        ...array_merge(
                            [Constraint::length(3)], // Header info
                            array_map(fn() => Constraint::length(3), $this->downloads), // One constraint per download
                            [Constraint::min(0)] // Remaining space
                        )
                    )
                    ->widgets(
                        // Header
                        BlockWidget::default()
                            ->borders(Borders::ALL)
                            ->style(Style::default()->fg(AnsiColor::Cyan))
                            ->widget(
                                ParagraphWidget::fromLines(
                                    Line::fromString('📡 Simulated Download Manager - Live Animation'),
                                    Line::fromString('Watch the progress bars update in real-time!'),
                                )
                            ),
                        // Downloads
                        ...array_map(function ($download) {
                            $ratio = $download['size'] > 0 ? min(1.0, $download['downloaded'] / $download['size']) : 0;
                            $percentage = round($ratio * 100);
                            $status = $ratio >= 1.0 ? 'Complete' : 'Downloading';
                            
                            return GridWidget::default()
                                ->direction(Direction::Horizontal)
                                ->constraints(
                                    Constraint::length(30),
                                    Constraint::min(0),
                                )
                                ->widgets(
                                    ParagraphWidget::fromLines(
                                        Line::fromSpans(
                                            Span::fromString($status)->fg($ratio >= 1.0 ? AnsiColor::Green : AnsiColor::Yellow),
                                        ),
                                        Line::fromSpans(
                                            Span::fromString($download['name'])->fg(AnsiColor::White),
                                        ),
                                        Line::fromSpans(
                                            Span::fromString(sprintf('%d/%d MB', 
                                                intval($download['downloaded']), 
                                                $download['size']
                                            ))->fg(AnsiColor::Gray),
                                        ),
                                    ),
                                    GaugeWidget::default()
                                        ->ratio($ratio)
                                        ->label(Span::fromString(sprintf('%d%%', $percentage))->fg(AnsiColor::White))
                                        ->style(Style::default()->fg(match (true) {
                                            $ratio >= 1.0 => AnsiColor::Green,
                                            $ratio >= 0.7 => AnsiColor::Yellow,
                                            $ratio >= 0.3 => AnsiColor::Cyan,
                                            default => AnsiColor::Red,
                                        }))
                                );
                        }, $this->downloads)
                    )
            );
    }

    /**
     * Draw a digit (0-9) using rectangles in the canvas
     */
    private function drawDigit(CanvasContext $context, int $digit, float $x, float $y, AnsiColor $color): void
    {
        // Simple 7-segment display style using rectangles
        $segments = [
            0 => [1,1,1,0,1,1,1], // 0
            1 => [0,0,1,0,0,1,0], // 1
            2 => [1,0,1,1,1,0,1], // 2
            3 => [1,0,1,1,0,1,1], // 3
            4 => [0,1,1,1,0,1,0], // 4
            5 => [1,1,0,1,0,1,1], // 5
            6 => [1,1,0,1,1,1,1], // 6
            7 => [1,0,1,0,0,1,0], // 7
            8 => [1,1,1,1,1,1,1], // 8
            9 => [1,1,1,1,0,1,1], // 9
        ];

        if (!isset($segments[$digit])) {
            return;
        }

        $pattern = $segments[$digit];
        
        // Draw 7-segment display
        // Top horizontal
        if ($pattern[0]) {
            $context->draw(new RectangleShape(
                position: FloatPosition::at($x + 1, $y),
                width: 3,
                height: 1,
                color: $color
            ));
        }
        
        // Top left vertical
        if ($pattern[1]) {
            $context->draw(new RectangleShape(
                position: FloatPosition::at($x, $y + 1),
                width: 1,
                height: 2,
                color: $color
            ));
        }
        
        // Top right vertical
        if ($pattern[2]) {
            $context->draw(new RectangleShape(
                position: FloatPosition::at($x + 4, $y + 1),
                width: 1,
                height: 2,
                color: $color
            ));
        }
        
        // Middle horizontal
        if ($pattern[3]) {
            $context->draw(new RectangleShape(
                position: FloatPosition::at($x + 1, $y + 3),
                width: 3,
                height: 1,
                color: $color
            ));
        }
        
        // Bottom left vertical
        if ($pattern[4]) {
            $context->draw(new RectangleShape(
                position: FloatPosition::at($x, $y + 4),
                width: 1,
                height: 2,
                color: $color
            ));
        }
        
        // Bottom right vertical
        if ($pattern[5]) {
            $context->draw(new RectangleShape(
                position: FloatPosition::at($x + 4, $y + 4),
                width: 1,
                height: 2,
                color: $color
            ));
        }
        
        // Bottom horizontal
        if ($pattern[6]) {
            $context->draw(new RectangleShape(
                position: FloatPosition::at($x + 1, $y + 6),
                width: 3,
                height: 1,
                color: $color
            ));
        }
    }

    private function drawPHPElephant(CanvasContext $context, float $x, float $y, AnsiColor $color): void
    {
        // PHP Elephant sprite - simplified pixel art style
        // Body
        $context->draw(new RectangleShape(
            position: FloatPosition::at($x + 2, $y + 3),
            width: 6,
            height: 4,
            color: $color
        ));
        
        // Head
        $context->draw(new RectangleShape(
            position: FloatPosition::at($x, $y + 1),
            width: 4,
            height: 3,
            color: $color
        ));
        
        // Trunk
        $context->draw(new RectangleShape(
            position: FloatPosition::at($x - 1, $y + 2),
            width: 2,
            height: 1,
            color: $color
        ));
        
        // Legs
        for ($i = 0; $i < 4; $i++) {
            $context->draw(new RectangleShape(
                position: FloatPosition::at($x + 2 + $i * 1.5, $y + 7),
                width: 1,
                height: 2,
                color: $color
            ));
        }
        
        // Ear
        $context->draw(new RectangleShape(
            position: FloatPosition::at($x + 3, $y),
            width: 2,
            height: 2,
            color: $color
        ));
    }

    private function drawGithubCat(CanvasContext $context, float $x, float $y, AnsiColor $color): void
    {
        // Octocat-inspired sprite
        // Head (circle-ish)
        $context->draw(new CircleShape(
            position: FloatPosition::at($x + 2, $y + 2),
            radius: 2.0,
            color: $color
        ));
        
        // Eyes
        $context->draw(new RectangleShape(
            position: FloatPosition::at($x + 1, $y + 1),
            width: 1,
            height: 1,
            color: AnsiColor::Black
        ));
        $context->draw(new RectangleShape(
            position: FloatPosition::at($x + 3, $y + 1),
            width: 1,
            height: 1,
            color: AnsiColor::Black
        ));
        
        // Tentacles
        for ($i = 0; $i < 4; $i++) {
            $tentacleX = $x - 1 + $i * 1.5;
            $tentacleY = $y + 4 + sin($tentacleX * 0.5) * 2;
            $context->draw(new LineShape(
                point1: FloatPosition::at($x + 2, $y + 4),
                point2: FloatPosition::at($tentacleX, $tentacleY),
                color: $color
            ));
        }
    }

    private function drawArrowSprite(CanvasContext $context, float $x, float $y, float $angle, AnsiColor $color): void
    {
        // Rotating arrow sprite
        $length = 4;
        $radians = deg2rad($angle);
        
        // Arrow shaft
        $endX = $x + $length * cos($radians);
        $endY = $y + $length * sin($radians);
        
        $context->draw(new LineShape(
            point1: FloatPosition::at($x, $y),
            point2: FloatPosition::at($endX, $endY),
            color: $color
        ));
        
        // Arrow head
        $headAngle1 = $radians + 2.5;
        $headAngle2 = $radians - 2.5;
        $headLength = 2;
        
        $context->draw(new LineShape(
            point1: FloatPosition::at($endX, $endY),
            point2: FloatPosition::at($endX - $headLength * cos($headAngle1), $endY - $headLength * sin($headAngle1)),
            color: $color
        ));
        
        $context->draw(new LineShape(
            point1: FloatPosition::at($endX, $endY),
            point2: FloatPosition::at($endX - $headLength * cos($headAngle2), $endY - $headLength * sin($headAngle2)),
            color: $color
        ));
    }

    private function drawStickFigure(CanvasContext $context, float $x, float $y, int $walkCycle, AnsiColor $color): void
    {
        // Animated walking stick figure
        // Head
        $context->draw(new CircleShape(
            position: FloatPosition::at($x, $y),
            radius: 1.0,
            color: $color
        ));
        
        // Body
        $context->draw(new LineShape(
            point1: FloatPosition::at($x, $y + 1),
            point2: FloatPosition::at($x, $y + 4),
            color: $color
        ));
        
        // Arms
        $armOffset = sin($walkCycle * 0.5) * 0.5;
        $context->draw(new LineShape(
            point1: FloatPosition::at($x, $y + 2),
            point2: FloatPosition::at($x - 1 + $armOffset, $y + 3),
            color: $color
        ));
        $context->draw(new LineShape(
            point1: FloatPosition::at($x, $y + 2),
            point2: FloatPosition::at($x + 1 - $armOffset, $y + 3),
            color: $color
        ));
        
        // Legs (walking animation)
        $leg1Offset = sin($walkCycle) * 1;
        $leg2Offset = sin($walkCycle + 3.14) * 1;
        
        $context->draw(new LineShape(
            point1: FloatPosition::at($x, $y + 4),
            point2: FloatPosition::at($x - 0.5 + $leg1Offset, $y + 6),
            color: $color
        ));
        $context->draw(new LineShape(
            point1: FloatPosition::at($x, $y + 4),
            point2: FloatPosition::at($x + 0.5 + $leg2Offset, $y + 6),
            color: $color
        ));
    }

    private function drawStar(CanvasContext $context, float $x, float $y, AnsiColor $color): void
    {
        // Simple star sprite
        $context->draw(new LineShape(
            point1: FloatPosition::at($x, $y - 1),
            point2: FloatPosition::at($x, $y + 1),
            color: $color
        ));
        $context->draw(new LineShape(
            point1: FloatPosition::at($x - 1, $y),
            point2: FloatPosition::at($x + 1, $y),
            color: $color
        ));
    }

    private function drawPixelText(CanvasContext $context, float $x, float $y, string $text, AnsiColor $color): void
    {
        // Simple pixel text rendering
        $chars = str_split($text);
        $charWidth = 4;
        
        foreach ($chars as $i => $char) {
            $charX = $x + $i * $charWidth;
            
            if ($char === ' ') continue;
            
            // Simple character patterns (just rectangles for demo)
            switch (strtoupper($char)) {
                case 'P':
                    $context->draw(new RectangleShape(
                        position: FloatPosition::at($charX, $y),
                        width: 1,
                        height: 5,
                        color: $color
                    ));
                    $context->draw(new RectangleShape(
                        position: FloatPosition::at($charX, $y),
                        width: 2,
                        height: 1,
                        color: $color
                    ));
                    $context->draw(new RectangleShape(
                        position: FloatPosition::at($charX, $y + 2),
                        width: 2,
                        height: 1,
                        color: $color
                    ));
                    break;
                default:
                    // Default character - just a block
                    $context->draw(new RectangleShape(
                        position: FloatPosition::at($charX, $y),
                        width: 2,
                        height: 4,
                        color: $color
                    ));
            }
        }
    }

	private function drawLaravelLogo(CanvasContext $context, float $x, float $y, AnsiColor $color): void
	{
		// Draw Laravel logo using shapes - positioned to be visible
		// L
		$context->draw(new RectangleShape(
			position: FloatPosition::at($x, $y),
			width: 1,
			height: 5,
			color: $color
		));
		$context->draw(new RectangleShape(
			position: FloatPosition::at($x, $y + 4),
			width: 3,
			height: 1,
			color: $color
		));

		// A
		$context->draw(new LineShape(
			point1: FloatPosition::at($x + 5, $y + 5),
			point2: FloatPosition::at($x + 6, $y),
			color: $color
		));
		$context->draw(new LineShape(
			point1: FloatPosition::at($x + 6, $y),
			point2: FloatPosition::at($x + 7, $y + 5),
			color: $color
		));
		$context->draw(new RectangleShape(
			position: FloatPosition::at($x + 5, $y + 3),
			width: 2,
			height: 1,
			color: $color
		));

		// R
		$context->draw(new RectangleShape(
			position: FloatPosition::at($x + 9, $y),
			width: 1,
			height: 5,
			color: $color
		));
		$context->draw(new RectangleShape(
			position: FloatPosition::at($x + 9, $y),
			width: 2,
			height: 1,
			color: $color
		));
		$context->draw(new RectangleShape(
			position: FloatPosition::at($x + 9, $y + 2),
			width: 2,
			height: 1,
			color: $color
		));
		$context->draw(new RectangleShape(
			position: FloatPosition::at($x + 11, $y),
			width: 1,
			height: 3,
			color: $color
		));
		$context->draw(new LineShape(
			point1: FloatPosition::at($x + 10, $y + 3),
			point2: FloatPosition::at($x + 11, $y + 5),
			color: $color
		));
	}

    /**
     * Create a pixel art representation of the Laravel logo
     */
    private function createLaravelLogoPixelArt(): array
    {
        $imagePath = __DIR__.'/../../../resources/images/laravel-logo.png';
//        dd($imagePath);
        // If the image exists, try to convert it to pixel art
        if (file_exists($imagePath)) {
            try {
                // Get image dimensions first
                $imageInfo = getimagesize($imagePath);
                if ($imageInfo === false) {
                    throw new Exception("Cannot get image size");
                }
                
                $origWidth = $imageInfo[0];
                $origHeight = $imageInfo[1];
                
                // Calculate target dimensions based on image aspect ratio
                // Target canvas is roughly 100 wide (accounting for terminal character aspect ratio)
                $maxCanvasWidth = 100;
                $maxCanvasHeight = 50; // Terminal characters are ~2:1 ratio
                
                // Calculate scaling to fit within canvas while maintaining aspect ratio
                $scaleX = $maxCanvasWidth / $origWidth;
                $scaleY = $maxCanvasHeight / $origHeight;
                $scale = min($scaleX, $scaleY); // Use smaller scale to fit entirely
                
                $targetWidth = (int)($origWidth * $scale);
                $targetHeight = (int)($origHeight * $scale);
                
                return $this->convertImageToPixelArt($imagePath, $targetWidth, $targetHeight);
            } catch (Exception $e) {
                // Fall back to manual pixel art if image conversion fails
                $this->info("Image conversion failed: " . $e->getMessage());
            }
        }
        
        // Fallback: manual pixel art version of Laravel logo (improved) - 100x50
        $fallbackLogo = [];
        // Create a 100x50 black background
        for ($y = 0; $y < 50; $y++) {
            $row = [];
            for ($x = 0; $x < 100; $x++) {
                $row[] = '#000000';
            }
            $fallbackLogo[] = $row;
        }
        
        // Add centered Laravel logo pattern (simplified version)
        $logoPattern = [
            [0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0],
            [0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0],
            [0,1,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,1,0],
            [0,1,2,1,1,1,1,2,2,1,1,1,1,2,2,2,2,2,1,0],
            [0,1,2,1,2,2,1,2,1,1,2,2,1,2,2,2,2,2,1,0],
            [0,1,2,1,2,2,1,2,1,1,2,2,1,2,2,2,2,2,1,0],
            [0,1,2,1,1,1,1,2,1,1,1,1,1,2,2,2,2,2,1,0],
            [0,1,2,1,2,2,2,2,1,1,2,2,2,2,2,2,2,2,1,0],
            [0,1,2,1,2,2,2,2,1,1,2,2,2,2,2,2,2,2,1,0],
            [0,1,2,1,1,1,1,2,1,1,1,1,1,2,2,2,2,2,1,0],
            [0,1,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,1,0],
            [0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0],
            [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
        ];
        
        // Map colors: 0 = black, 1 = Laravel red, 2 = white
        $colorMap = ['#000000', '#ff2d20', '#ffffff'];
        
        // Place the pattern centered in the 100x50 canvas
        $startY = (50 - count($logoPattern)) / 2;
        $startX = (100 - 20) / 2;
        
        foreach ($logoPattern as $rowIndex => $row) {
            $y = $startY + $rowIndex;
            if ($y >= 0 && $y < 50) {
                foreach ($row as $colIndex => $colorIndex) {
                    $x = $startX + $colIndex;
                    if ($x >= 0 && $x < 100) {
                        $fallbackLogo[$y][$x] = $colorMap[$colorIndex];
                    }
                }
            }
        }
        
        return $fallbackLogo;
    }

    /**
     * Get all available logo files from the images directory  
     */
    private function getAvailableLogos(): array
    {
        $imagesDir = __DIR__.'/../../../resources/images/';
        $logos = [];
        
        if (is_dir($imagesDir)) {
            $files = glob($imagesDir . '*.{png,jpg,jpeg,gif}', GLOB_BRACE);
            foreach ($files as $file) {
                $basename = basename($file, '.' . pathinfo($file, PATHINFO_EXTENSION));
                $logos[$basename] = $file;
            }
        }
        
        return $logos;
    }

    /**
     * Get cached logo for canvas sprites (with automatic scaling for performance)
     */
    private function getCachedLogo(string $logoName): array
    {
        if (!isset($this->cachedLogos[$logoName])) {
            $logos = $this->getAvailableLogos();
//            dd($logos);
            if (isset($logos[$logoName])) {
                $imagePath = $logos[$logoName];
                
                try {
                    // Get image dimensions first
                    $imageInfo = getimagesize($imagePath);
                    if ($imageInfo !== false) {
                        $origWidth = $imageInfo[0];
                        $origHeight = $imageInfo[1];
//                        dd($imageInfo);
                        // Calculate target dimensions for sprite (reduced by 20%)
                        $maxCanvasWidth = 60;  // Reduced from 40 by 20%
                        $maxCanvasHeight = 30; // Reduced from 20 by 20%
                        
                        // Calculate scaling to fit within canvas while maintaining aspect ratio
                        $scaleX = $maxCanvasWidth / $origWidth;
                        $scaleY = $maxCanvasHeight / $origHeight;
                        $scale = min($scaleX, $scaleY);
                        
                        $targetWidth = (int)($origWidth * $scale);
                        $targetHeight = (int)($origHeight * $scale);
                        
                        $this->cachedLogos[$logoName] = $this->convertImageToPixelArt($imagePath, $targetWidth, $targetHeight);
                    }
                } catch (Exception $e) {
                    // Fallback based on logo name
                    $this->cachedLogos[$logoName] = $this->getFallbackLogo($logoName);
                }
            } else {
                // Logo file not found, use fallback
                $this->cachedLogos[$logoName] = $this->getFallbackLogo($logoName);
            }
        }
        
        return $this->cachedLogos[$logoName];
    }

    /**
     * Get fallback logo pattern based on name
     */
    private function getFallbackLogo(string $logoName): array
    {
        return match(strtolower($logoName)) {
            'laravel-logo' => [
                ['#ff2d20', '#ff2d20', '#ff2d20', '#ff2d20', '#ff2d20'],
                ['#ff2d20', '#ffffff', '#ffffff', '#ffffff', '#ff2d20'],
                ['#ff2d20', '#ff2d20', '#ff2d20', '#ff2d20', '#ff2d20'],
            ],
            'php-logo' => [
                ['#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF'],
                ['#8892BF', '#ffffff', '#000000', '#ffffff', '#8892BF'],
                ['#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF'],
            ],
            'vue-logo' => [
                ['#4FC08D', '#4FC08D', '#34495E', '#4FC08D', '#4FC08D'],
                ['#4FC08D', '#34495E', '#34495E', '#34495E', '#4FC08D'],
                ['#4FC08D', '#4FC08D', '#34495E', '#4FC08D', '#4FC08D'],
            ],
            'react-logo' => [
                ['#61DAFB', '#61DAFB', '#61DAFB', '#61DAFB', '#61DAFB'],
                ['#61DAFB', '#20232A', '#61DAFB', '#20232A', '#61DAFB'],
                ['#61DAFB', '#61DAFB', '#61DAFB', '#61DAFB', '#61DAFB'],
            ],
            default => [
                ['#666666', '#666666', '#666666', '#666666', '#666666'],
                ['#666666', '#ffffff', '#666666', '#ffffff', '#666666'],
                ['#666666', '#666666', '#666666', '#666666', '#666666'],
            ]
        };
    }

    /**
     * Initialize logo sprites with random positions and velocities
     */
    private function initializeLogoSprites(): void
    {
        if (empty($this->logoSprites)) {
            $availableLogos = array_keys($this->getAvailableLogos());
            
            // If no logos found, use fallback names
            if (empty($availableLogos)) {
                $availableLogos = ['laravel-logo', 'php-logo', 'vue-logo', 'react-logo'];
            }
            
            // Create sprites for available logos (limit to 4 for performance)
            $logosToUse = array_slice($availableLogos, 0, 4);
            
            foreach ($logosToUse as $index => $logoName) {
                $this->logoSprites[] = [
                    'name' => $logoName,
                    'x' => 20 + ($index * 15), // Spread them out initially
                    'y' => 10 + ($index * 5),
                    'vx' => 0.5 + ($index * 0.3), // Different speeds
                    'vy' => 0.3 + ($index * 0.2),
                    'angle' => $index * 90, // Different starting angles for orbit
                ];
            }
        }
    }

    /**
     * Update logo sprite positions and render them on canvas
     */
    private function updateAndRenderLogoSprites(CanvasContext $context, float $time): void
    {
        foreach ($this->logoSprites as $index => &$sprite) {
            // Update sprite position with different movement patterns
            switch ($index % 3) {
                case 0: // Circular orbit
                    $radius = 15;
                    $centerX = 50;
                    $centerY = 25;
                    $angle = $time * 0.5 + $sprite['angle'] * (pi() / 180);
                    $sprite['x'] = $centerX + cos($angle) * $radius;
                    $sprite['y'] = $centerY + sin($angle) * $radius * 0.5; // Elliptical
                    break;
                    
                case 1: // Bouncing motion
                    $sprite['x'] += $sprite['vx'];
                    $sprite['y'] += $sprite['vy'];
                    
                    // Bounce off canvas edges
                    if ($sprite['x'] <= 0 || $sprite['x'] >= 85) {
                        $sprite['vx'] *= -1;
                    }
                    if ($sprite['y'] <= 0 || $sprite['y'] >= 40) {
                        $sprite['vy'] *= -1;
                    }
                    break;
                    
                case 2: // Figure-8 pattern  
                    $t = $time * 0.3 + $sprite['angle'] * 0.01;
                    $sprite['x'] = 25 + sin($t) * 20;
                    $sprite['y'] = 15 + sin($t * 2) * 10;
                    break;
            }
            
            // Use the same high-quality image conversion as the Images tab (no scaling for testing)
            if ($sprite['name'] === 'laravel-logo') {
                $logoPixels = $this->createLaravelLogoPixelArt();
                $this->renderPixelArtToCanvas($context, $logoPixels, $sprite['x'], $sprite['y']);
            } else {
                // For other logos, use the cached version
                $logoPixels = $this->getCachedLogo($sprite['name']);
                $this->renderLogoSpriteToCanvas($context, $logoPixels, $sprite['x'], $sprite['y']);
            }
        }
    }

    /**
     * Convert an image file to pixel art array
     */
    private function convertImageToPixelArt(string $imagePath, int $width, int $height): array
    {
        if (!extension_loaded('gd')) {
            throw new Exception("GD extension not loaded");
        }
        
        // Create image resource from file
        $imageInfo = getimagesize($imagePath);
        if ($imageInfo === false) {
            throw new Exception("Cannot get image size");
        }
        
        $image = match ($imageInfo[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($imagePath),
            IMAGETYPE_PNG => imagecreatefrompng($imagePath),
            IMAGETYPE_GIF => imagecreatefromgif($imagePath),
            default => throw new Exception("Unsupported image format")
        };
        
        if ($image === false) {
            throw new Exception("Cannot create image resource");
        }
        
        // Get original dimensions
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);
        
        // Create a resized image (dimensions already calculated to fit properly)
        $resizedImage = imagecreatetruecolor($width, $height);
        if ($resizedImage === false) {
            imagedestroy($image);
            throw new Exception("Cannot create resized image");
        }
        
        // Preserve transparency for PNGs
        imagealphablending($resizedImage, false);
        imagesavealpha($resizedImage, true);
        
        // Fill with transparent black background
        $transparent = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
        imagefill($resizedImage, 0, 0, $transparent);
        
        // Resize the image to exact target dimensions
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);
        
        // Convert to pixel array (flip vertically to correct orientation)
        $pixelArt = [];
        for ($y = $height - 1; $y >= 0; $y--) { // Flip Y coordinate
            $row = [];
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($resizedImage, $x, $y);
                
                // Extract RGB values
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                
                // Get alpha value
                $alpha = ($rgb >> 24) & 0x7F;
                
                // Convert to hex (handle transparency)
                if ($alpha >= 64) { // High alpha = transparent
                    $row[] = '#000000';
                } else {
                    $row[] = sprintf('#%02x%02x%02x', $r, $g, $b);
                }
            }
            $pixelArt[] = $row;
        }
        
        // Clean up
        imagedestroy($image);
        imagedestroy($resizedImage);
        
        return $pixelArt;
    }

    /**
     * Create a pixel art representation of the PHP logo
     */
    private function createPHPLogoPixelArt(): array
    {
        // Create a 20x15 PHP elephant pixel art
        return [
            ['#000000', '#000000', '#000000', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000'],
            ['#000000', '#000000', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000'],
            ['#000000', '#8892BF', '#8892BF', '#ffffff', '#ffffff', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000'],
            ['#000000', '#8892BF', '#ffffff', '#000000', '#ffffff', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000'],
            ['#8892BF', '#8892BF', '#ffffff', '#ffffff', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000'],
            ['#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000'],
            ['#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000'],
            ['#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000'],
            ['#000000', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#8892BF', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000'],
            ['#000000', '#000000', '#8892BF', '#8892BF', '#000000', '#8892BF', '#8892BF', '#000000', '#8892BF', '#8892BF', '#000000', '#8892BF', '#8892BF', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000'],
            ['#000000', '#000000', '#8892BF', '#8892BF', '#000000', '#8892BF', '#8892BF', '#000000', '#8892BF', '#8892BF', '#000000', '#8892BF', '#8892BF', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000'],
            ['#000000', '#000000', '#8892BF', '#8892BF', '#000000', '#8892BF', '#8892BF', '#000000', '#8892BF', '#8892BF', '#000000', '#8892BF', '#8892BF', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000'],
            ['#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000'],
            ['#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000'],
            ['#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000'],
        ];
    }

    /**
     * Create a pattern pixel art
     */
    private function createPatternPixelArt(): array
    {
        $pattern = [];
        for ($y = 0; $y < 15; $y++) {
            $row = [];
            for ($x = 0; $x < 20; $x++) {
                if (($x + $y) % 3 === 0) {
                    $row[] = '#ff6b6b';
                } elseif (($x + $y) % 3 === 1) {
                    $row[] = '#4ecdc4';
                } else {
                    $row[] = '#45b7d1';
                }
            }
            $pattern[] = $row;
        }
        return $pattern;
    }

    /**
     * Create an animated gradient
     */
    private function createAnimatedGradient(): array
    {
        $gradient = [];
        $time = $this->animationFrame * 0.1;
        
        for ($y = 0; $y < 15; $y++) {
            $row = [];
            for ($x = 0; $x < 30; $x++) {
                $wave = sin(($x + $time) * 0.2) * 0.5 + 0.5;
                $red = intval(255 * $wave);
                $green = intval(255 * (1 - $wave));
                $blue = intval(255 * sin(($y + $time) * 0.3) * 0.5 + 0.5);
                
                $row[] = sprintf('#%02x%02x%02x', $red, $green, $blue);
            }
            $gradient[] = $row;
        }
        return $gradient;
    }

    /**
     * Create an animated face
     */
    private function createAnimatedFace(): array
    {
        $face = [];
        $blink = ($this->animationFrame % 60) < 5; // Blink every 60 frames for 5 frames
        
        for ($y = 0; $y < 15; $y++) {
            $row = [];
            for ($x = 0; $x < 15; $x++) {
                $color = '#000000'; // Default background
                
                // Face outline (circle)
                $centerX = 7;
                $centerY = 7;
                $distance = sqrt(($x - $centerX) ** 2 + ($y - $centerY) ** 2);
                
                if ($distance <= 6) {
                    $color = '#ffcc99'; // Skin color
                    
                    // Eyes
                    if (!$blink && $y === 5) {
                        if ($x === 4 || $x === 10) {
                            $color = '#000000'; // Eye
                        }
                    } elseif ($blink && $y === 5) {
                        if ($x >= 3 && $x <= 5) {
                            $color = '#000000'; // Closed eye
                        }
                        if ($x >= 9 && $x <= 11) {
                            $color = '#000000'; // Closed eye
                        }
                    }
                    
                    // Mouth
                    if ($y === 9 && $x >= 5 && $x <= 9) {
                        $color = '#000000'; // Mouth
                    }
                    
                    // Nose
                    if ($y === 7 && $x === 7) {
                        $color = '#ff9999'; // Nose
                    }
                }
                
                $row[] = $color;
            }
            $face[] = $row;
        }
        return $face;
    }

    /**
     * Scale pixel art by a given factor
     */
    private function scalePixelArt(array $pixelArt, float $scale): array
    {
        if (empty($pixelArt)) {
            return [];
        }
        
        $originalHeight = count($pixelArt);
        $originalWidth = count($pixelArt[0]);
        
        $newHeight = max(1, (int)($originalHeight * $scale));
        $newWidth = max(1, (int)($originalWidth * $scale));
        
        $scaled = [];
        
        for ($y = 0; $y < $newHeight; $y++) {
            $row = [];
            for ($x = 0; $x < $newWidth; $x++) {
                // Map scaled coordinates back to original coordinates
                $origX = min($originalWidth - 1, (int)($x / $scale));
                $origY = min($originalHeight - 1, (int)($y / $scale));
                
                $row[] = $pixelArt[$origY][$origX];
            }
            $scaled[] = $row;
        }
        
        return $scaled;
    }

    /**
     * Render logo sprites to canvas with better visibility
     */
    private function renderLogoSpriteToCanvas(CanvasContext $context, array $pixelArt, float $startX, float $startY): void
    {
        foreach ($pixelArt as $y => $row) {
            foreach ($row as $x => $colorHex) {
                // Convert hex color to RGB
                $rgb = $this->hexToRgb($colorHex);
                if ($rgb) {
                    // Skip completely transparent/black pixels for logos
                    if ($colorHex === '#000000') {
                        continue;
                    }
                    
                    $color = RgbColor::fromRgb($rgb['r'], $rgb['g'], $rgb['b']);
                    
                    // Draw larger rectangles for better visibility (2x2 instead of 1x1)
                    $context->draw(new RectangleShape(
                        position: FloatPosition::at($startX + ($x * 2), $startY + ($y * 2)),
                        width: 2,
                        height: 2,
                        color: $color
                    ));
                }
            }
        }
    }

    /**
     * Render a pixel art array to the canvas
     */
    private function renderPixelArtToCanvas(CanvasContext $context, array $pixelArt, float $startX, float $startY): void
    {
        foreach ($pixelArt as $y => $row) {
            foreach ($row as $x => $colorHex) {
                if ($colorHex === '#000000') {
                    continue; // Skip black pixels (transparent)
                }
                
                // Convert hex color to RGB
                $rgb = $this->hexToRgb($colorHex);
                if ($rgb) {
                    $color = RgbColor::fromRgb($rgb['r'], $rgb['g'], $rgb['b']);
                    
                    // Draw a small rectangle for each pixel
                    $context->draw(new RectangleShape(
                        position: FloatPosition::at($startX + $x, $startY + $y),
                        width: 1,
                        height: 1,
                        color: $color
                    ));
                }
            }
        }
    }

    /**
     * Convert hex color to RGB array
     */
    private function hexToRgb(string $hex): ?array
    {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) === 6) {
            return [
                'r' => hexdec(substr($hex, 0, 2)),
                'g' => hexdec(substr($hex, 2, 2)),
                'b' => hexdec(substr($hex, 4, 2)),
            ];
        }
        
        return null;
    }
}
