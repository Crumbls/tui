<?php

declare(strict_types=1);

namespace Crumbls\Tui;

use Crumbls\Tui\Console\Commands\TuiDemoCommand;
use Illuminate\Support\ServiceProvider;

/**
 * Minimal TUI Service Provider - fresh start.
 */
class TuiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge config if it exists
        if (file_exists(__DIR__.'/../config/tui.php')) {
            $this->mergeConfigFrom(
                __DIR__.'/../config/tui.php',
                'tui'
            );
        }
        
        // For now, no complex bindings - we'll add as needed
    }

    public function boot(): void
    {
	    if ($this->app->runningInConsole()) {
		    $this->commands([
				TuiDemoCommand::class
		    ]);
	    }
    }
}