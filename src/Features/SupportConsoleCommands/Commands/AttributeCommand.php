<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'livewire:attribute')]
class AttributeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'livewire:attribute {name} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Livewire attribute class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Attribute';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    public function getStub()
    {
        return __DIR__ . '/livewire.attribute.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    public function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Livewire\Attributes';
    }
}
