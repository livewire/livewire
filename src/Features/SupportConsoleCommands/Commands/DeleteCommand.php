<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

use Illuminate\Support\Facades\File;

class DeleteCommand extends FileManipulationCommand
{
    protected $signature = 'livewire:delete {name} {--inline} {--force} {--test}';

    protected $description = 'Delete a Livewire component';

    public function handle()
    {
        $this->parser = new ComponentParser(
            config('livewire.class_namespace'),
            config('livewire.view_path'),
            $this->argument('name')
        );

        if (! $force = $this->option('force')) {
            $shouldContinue = $this->components->confirm(
                "Are you sure you want to delete the following files? \n\n{$this->parser->relativeClassPath()}\n{$this->parser->relativeViewPath()}\n"
            );

            if (! $shouldContinue) {
                return;
            }
        }

        $inline = $this->option('inline');
        $test = $this->option('test');

        $class = $this->removeClass($force);
        if (! $inline) $view = $this->removeView($force);
        if ($test) $test = $this->removeTest($force);

        $this->components->warn('COMPONENT DESTROYED 🦖💫');
        $class && $this->components->warn("CLASS:</> {$this->parser->relativeClassPath()}");
        if (! $inline) $view && $this->components->warn("VIEW: {$this->parser->relativeViewPath()}");
        $test && $this->components->warn("Test: {$this->parser->relativeTestPath()}");
    }

    protected function removeTest($force = false)
    {
        $testPath = $this->parser->testPath();

        if (! File::exists($testPath) && ! $force) {
            $this->components->error("WHOOPS-IE-TOOTLES 😳");
            $this->components->error("Test doesn't exist: {$this->parser->relativeTestPath()}");

            return false;
        }

        File::delete($testPath);

        return $testPath;
    }

    protected function removeClass($force = false)
    {
        $classPath = $this->parser->classPath();

        if (! File::exists($classPath) && ! $force) {
            $this->components->error("WHOOPS-IE-TOOTLES 😳");
            $this->components->error("Class doesn't exist: {$this->parser->relativeClassPath()}");

            return false;
        }

        File::delete($classPath);

        return $classPath;
    }

    protected function removeView($force = false)
    {
        $viewPath = $this->parser->viewPath();

        if (! File::exists($viewPath) && ! $force) {
            $this->components->error("View doesn't exist: {$this->parser->relativeViewPath()}");

            return false;
        }

        File::delete($viewPath);

        return $viewPath;
    }
}
