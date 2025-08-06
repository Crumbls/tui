<?php

namespace Crumbls\Tui\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeTuiCommand extends GeneratorCommand
{
    protected $name = 'make:tui-command';
    protected $description = 'Create a new TUI command class';
    protected $type = 'TUI Command';

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__.'/stubs/tui-command.stub';
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Console\Commands';
    }

    /**
     * Build the class with the given name.
     */
    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        return $this->replaceCommand($stub, $name);
    }

    /**
     * Replace the command name for the given stub.
     */
    protected function replaceCommand(string $stub, string $name): string
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);
        $command = Str::kebab(str_replace('Command', '', $class));

        return str_replace('{{ command }}', $command, $stub);
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the command class'],
        ];
    }
}