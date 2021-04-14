<?php

namespace Livewire\Commands;

use Illuminate\Support\Facades\File;

class CopyCommand extends FileManipulationCommand
{
    protected $signature = 'livewire:copy {name} {new-name} {--inline} {--force}';

    protected $description = 'Copy a Livewire component';

    public function handle()
    {
        $this->parser = new ComponentParser(
            config('livewire.class_namespace'),
            config('livewire.view_path'),
            $this->argument('name')
        );

        $this->newParser = new ComponentParserFromExistingComponent(
            config('livewire.class_namespace'),
            config('livewire.view_path'),
            $this->argument('new-name'),
            $this->parser
        );

        $force = $this->option('force');
        $inline = $this->option('inline');

        $class = $this->copyClass($force, $inline);
        if (! $inline) $view = $this->copyView($force);

        $this->refreshComponentAutodiscovery();

        $this->line("<options=bold,reverse;fg=green> COMPONENT COPIED </> 🤙\n");
        $class && $this->line("<options=bold;fg=green>CLASS:</> {$this->parser->relativeClassPath()} <options=bold;fg=green>=></> {$this->newParser->relativeClassPath()}");
        if (! $inline) $view && $this->line("<options=bold;fg=green>VIEW:</>  {$this->parser->relativeViewPath()} <options=bold;fg=green>=></> {$this->newParser->relativeViewPath()}");
    }

    protected function copyClass($force, $inline)
    {
        if (File::exists($this->newParser->classPath()) && ! $force) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Class already exists:</> {$this->newParser->relativeClassPath()}");

            return false;
        }

        $this->ensureDirectoryExists($this->newParser->classPath());

        return File::put($this->newParser->classPath(), $this->newParser->classContents($inline));
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
