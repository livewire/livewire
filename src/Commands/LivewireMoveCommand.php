<?php

namespace Livewire\Commands;

use Illuminate\Support\Facades\File;

class LivewireMoveCommand extends LivewireFileManipulationCommand
{
    protected $signature = 'livewire:move {name} {newName} {--force}';

    protected $description = 'Move a Livewire component. (Alias livewire:mv)';

    public function handle()
    {
        $this->parser = new LivewireFileManipulationCommandParser(
            config('livewire.class_namespace', 'App\\Http\\Livewire'),
            config('livewire.view_path', resource_path('views/livewire')),
            $this->argument('name'),
            $this->argument('newName')
        );

        $class = $this->renameClass();
        $view = $this->renameView();

        $this->refreshComponentAutodiscovery();

        ($class && $view) && $this->line("<options=bold,reverse;fg=green> COMPONENT MOVED </> 🤙\n");
        $class && $this->line("<options=bold;fg=green>CLASS:</> {$this->parser->component->relativeClassPath()} <options=bold;fg=green>=></> {$this->parser->newComponent->relativeClassPath()}");
        $view && $this->line("<options=bold;fg=green>VIEW:</>  {$this->parser->component->relativeViewPath()} <options=bold;fg=green>=></> {$this->parser->newComponent->relativeViewPath()}");
    }

    protected function renameClass()
    {
        if (File::exists($this->parser->newComponent->classPath())) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Class already exists:</> {$this->parser->newComponent->relativeClassPath()}");

            return false;
        }

        $this->ensureDirectoryExists($this->parser->newComponent->classPath());

        File::put($this->parser->newComponent->classPath(), $this->parser->newClassContents());

        return File::delete($this->parser->component->classPath());
    }

    protected function renameView()
    {
        $newViewPath = $this->parser->newComponent->viewPath();

        if (File::exists($newViewPath)) {
            $this->line("<fg=red;options=bold>View already exists:</> {$this->parser->newComponent->relativeViewPath()}");

            return false;
        }

        $this->ensureDirectoryExists($newViewPath);

        File::move($this->parser->component->viewPath(), $newViewPath);

        return $newViewPath;
    }
}
