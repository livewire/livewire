<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FormCommand extends GeneratorCommand
{
    protected $signature = 'livewire:form {name} {--model=} {--force}';

    protected $description = 'Create a new Livewire form class';

    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $model = $this->option('model') ?? 'App\Models\User';

        return $this->replaceModel($stub, $model);
    }

    public function getStub()
    {
        return __DIR__ . '/livewire.form.stub';
    }

    public function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Forms';
    }

    /**
     * Replace the model for the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @return string
     */
    protected function replaceModel($stub, $model)
    {
        $model = str_replace('/', '\\', $model);

        if (str_starts_with($model, '\\')) {
            $namespacedModel = trim($model, '\\');
        } else {
            $namespacedModel = $this->qualifyModel($model);
        }

        $model = class_basename(trim($model, '\\'));

        $stub = str_replace("{modelNamespace}", $namespacedModel, $stub);
        $stub = str_replace("{createExample}", $model.'::create($this->all());', $stub);

        return $stub;
    }
}
