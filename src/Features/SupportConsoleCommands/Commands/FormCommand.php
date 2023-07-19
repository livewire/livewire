<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

use Illuminate\Console\GeneratorCommand;

class FormCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'livewire:form {name} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Livewire form class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Form';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    public function getStub()
    {
        return __DIR__ . '/livewire.form.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    public function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Livewire\Forms';
    }
}
