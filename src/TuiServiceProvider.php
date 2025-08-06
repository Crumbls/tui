<?php

declare(strict_types=1);

namespace Crumbls\Tui;

use Crumbls\Tui\Console\Commands\TuiDemoCommand;
use Crumbls\Tui\Contracts\EventBusContract;
use Crumbls\Tui\Contracts\FocusBusContract;
use Crumbls\Tui\Contracts\HitTesterContract;
use Crumbls\Tui\Contracts\InputBusContract;
use Crumbls\Tui\Contracts\RendererContract;
use Crumbls\Tui\Contracts\ScreenContract;
use Crumbls\Tui\Contracts\TerminalContract;
use Illuminate\Support\ServiceProvider;

/**
 * TUI Service Provider for Laravel integration
 */
class TuiServiceProvider extends ServiceProvider
{
    /**
     * Register TUI services in the container
     */
    public function register(): void
    {
        // Merge package config with user config
        $this->mergeConfigFrom(
            __DIR__.'/../config/tui.php',
            'tui'
        );
        
        $this->registerContractBindings();
    }

    /**
     * Bootstrap TUI services
     */
    public function boot(): void
    {
        $this->publishConfig();
        $this->registerCommands();
    }

    /**
     * Register contract bindings using config
     */
    protected function registerContractBindings(): void
    {
        $bindings = config('tui.bindings', []);
        
        // Terminal system bindings
        $this->app->singleton(TerminalContract::class, function ($app) use ($bindings) {
            $terminalClass = $bindings['terminal'] ?? \Crumbls\Tui\Terminal\Terminal::class;
            
            $config = config('tui.terminal', []);
            return new $terminalClass(
                $config['width'] ?? null,
                $config['height'] ?? null
            );
        });

        $this->app->singleton(ScreenContract::class, function ($app) use ($bindings) {
            $screenClass = $bindings['screen'] ?? \Crumbls\Tui\Terminal\Screen::class;
            return new $screenClass($app->make(TerminalContract::class));
        });

        $this->app->singleton(RendererContract::class, function ($app) use ($bindings) {
            $rendererClass = $bindings['renderer'] ?? \Crumbls\Tui\Terminal\Renderer::class;
            return new $rendererClass($app->make(TerminalContract::class));
        });

        // Event and focus management bindings
        $this->app->singleton(FocusBusContract::class, function ($app) use ($bindings) {
            $focusBusClass = $bindings['focus_bus'] ?? \Crumbls\Tui\Terminal\FocusBus::class;
            return new $focusBusClass();
        });

        $this->app->singleton(InputBusContract::class, function ($app) use ($bindings) {
            $inputBusClass = $bindings['input_bus'] ?? \Crumbls\Tui\Terminal\InputBus::class;
            return new $inputBusClass();
        });

        $this->app->singleton(HitTesterContract::class, function ($app) use ($bindings) {
            $hitTesterClass = $bindings['hit_tester'] ?? \Crumbls\Tui\Terminal\HitTester::class;
            return new $hitTesterClass();
        });

        $this->app->singleton(EventBusContract::class, function ($app) use ($bindings) {
            $eventBusClass = $bindings['event_bus'] ?? \Crumbls\Tui\Terminal\EventBus::class;
            return new $eventBusClass();
        });

        // Convenience aliases for concrete classes
        $this->app->alias(TerminalContract::class, 'tui.terminal');
        $this->app->alias(RendererContract::class, 'tui.renderer');
        $this->app->alias(ScreenContract::class, 'tui.screen');
        $this->app->alias(FocusBusContract::class, 'tui.focus_bus');
        $this->app->alias(InputBusContract::class, 'tui.input_bus');
        $this->app->alias(HitTesterContract::class, 'tui.hit_tester');
        $this->app->alias(EventBusContract::class, 'tui.event_bus');
    }

    /**
     * Publish config file for user customization
     */
    protected function publishConfig(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/tui.php' => config_path('tui.php'),
            ], 'tui-config');
        }
    }

    /**
     * Register Artisan commands
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                TuiDemoCommand::class,
            ]);
        }
    }
}