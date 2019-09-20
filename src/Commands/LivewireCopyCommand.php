<?php

namespace Livewire\Commands;

use Illuminate\Support\Facades\File;

class LivewireCopyCommand extends LivewireFileManipulationCommand
{
    protected $signature = 'livewire:copy {name} {new-name} {--force}';

    protected $description = 'Copy a Livewire component. (Alias livewire:cp)';

    public function handle()
    {
        $this->parser = new LivewireFileManipulationCommandParser(
            config('livewire.class_namespace', 'App\\Http\\Livewire'),
            config('livewire.view_path', resource_path('views/livewire')),
            $this->argument('name'),
            $this->argument('new-name')
        );

        $force = $this->option('force');

        $class = $this->copyClass($force);
        $view = $this->copyView($force);

        $this->refreshComponentAutodiscovery();

        ($class && $view) && $this->line("<options=bold,reverse;fg=green> COMPONENT COPIED </> 🤙\n");
        $class && $this->line("<options=bold;fg=green>CLASS:</> {$this->parser->component->relativeClassPath()} <options=bold;fg=green>=></> {$this->parser->newComponent->relativeClassPath()}");
        $view && $this->line("<options=bold;fg=green>VIEW:</>  {$this->parser->component->relativeViewPath()} <options=bold;fg=green>=></> {$this->parser->newComponent->relativeViewPath()}");
    }

    protected function copyClass($force)
    {
        if (File::exists($this->parser->newComponent->classPath()) && ! $force) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Class already exists:</> {$this->parser->newComponent->relativeClassPath()}");

            return false;
        }

        $this->ensureDirectoryExists($this->parser->newComponent->classPath());

        return File::put($this->parser->newComponent->classPath(), $this->parser->newClassContents());
    }

    protected function copyView($force)
    {
        if (File::exists($this->parser->newComponent->viewPath()) && ! $force) {
            $this->line("<fg=red;options=bold>View already exists:</> {$this->parser->newComponent->relativeViewPath()}");

            return false;
        }

        $this->ensureDirectoryExists($this->parser->newComponent->viewPath());

        return File::copy("{$this->parser->component->viewPath()}", $this->parser->newComponent->viewPath());
    }
}
