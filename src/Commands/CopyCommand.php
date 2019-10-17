<?php

namespace Livewire\Commands;

use Illuminate\Support\Facades\File;

class CopyCommand extends FileManipulationCommand
{
    protected $signature = 'livewire:copy {name} {new-name} {--force}';

    protected $description = 'Copy a Livewire component.';

    public function handle()
    {
        $this->parser = new ComponentParser(
            config('livewire.class_namespace', 'App\\Http\\Livewire'),
            config('livewire.view_path', resource_path('views/livewire')),
            $this->argument('name')
        );

        $this->newParser = new ComponentParserFromExistingComponent(
            config('livewire.class_namespace', 'App\\Http\\Livewire'),
            config('livewire.view_path', resource_path('views/livewire')),
            $this->argument('new-name'),
            $this->parser
        );

        $force = $this->option('force');

        $class = $this->copyClass($force);
        $view = $this->copyView($force);

        $this->refreshComponentAutodiscovery();

        ($class && $view) && $this->line("<options=bold,reverse;fg=green> COMPONENT COPIED </> 🤙\n");
        $class && $this->line("<options=bold;fg=green>CLASS:</> {$this->parser->relativeClassPath()} <options=bold;fg=green>=></> {$this->newParser->relativeClassPath()}");
        $view && $this->line("<options=bold;fg=green>VIEW:</>  {$this->parser->relativeViewPath()} <options=bold;fg=green>=></> {$this->newParser->relativeViewPath()}");
    }

    protected function copyClass($force)
    {
        if (File::exists($this->newParser->classPath()) && ! $force) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Class already exists:</> {$this->newParser->relativeClassPath()}");

            return false;
        }

        $this->ensureDirectoryExists($this->newParser->classPath());

        return File::put($this->newParser->classPath(), $this->newParser->classContents());
    }

    protected function copyView($force)
    {
        if (File::exists($this->newParser->viewPath()) && ! $force) {
            $this->line("<fg=red;options=bold>View already exists:</> {$this->newParser->relativeViewPath()}");

            return false;
        }

        $this->ensureDirectoryExists($this->newParser->viewPath());

        return File::copy("{$this->parser->viewPath()}", $this->newParser->viewPath());
    }
}
