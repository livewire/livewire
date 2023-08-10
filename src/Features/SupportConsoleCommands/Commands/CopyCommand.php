<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

use Illuminate\Support\Facades\File;

class CopyCommand extends FileManipulationCommand
{
    protected $signature = 'livewire:copy {name} {new-name} {--inline} {--force} {--test}';

    protected $description = 'Copy a Livewire component';

    protected $newParser;

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
        $test = $this->option('test');

        $class = $this->copyClass($force, $inline);
        if (! $inline) $view = $this->copyView($force);
        if ($test){
            $test = $this->copyTest($force);
        }

        $this->components->info('COMPONENT COPIED 🤙');
        $class && $this->components->info("CLASS: {$this->parser->relativeClassPath()}");
        if (! $inline) $view && $this->components->info("VIEW {$this->parser->relativeViewPath()} => {$this->newParser->relativeViewPath()}");
        $test && $this->components->info("Test: {$this->parser->relativeTestPath()} => {$this->newParser->relativeTestPath()}");
    }

    protected function copyTest($force)
    {
        if (File::exists($this->newParser->testPath()) && ! $force) {
            $this->components->error('WHOOPS-IE-TOOTLES 😳');
            $this->components->error("Test already exists: {$this->newParser->relativeTestPath()}");

            return false;
        }

        $this->ensureDirectoryExists($this->newParser->testPath());

        return File::copy("{$this->parser->testPath()}", $this->newParser->testPath());
    }

    protected function copyClass($force, $inline)
    {
        if (File::exists($this->newParser->classPath()) && ! $force) {
            $this->components->error('WHOOPS-IE-TOOTLES 😳');
            $this->components->error("Class already exists: {$this->newParser->relativeClassPath()}");

            return false;
        }

        $this->ensureDirectoryExists($this->newParser->classPath());

        return File::put($this->newParser->classPath(), $this->newParser->classContents($inline));
    }

    protected function copyView($force)
    {
        if (File::exists($this->newParser->viewPath()) && ! $force) {
            $this->components->error("View already exists: {$this->newParser->relativeViewPath()}");

            return false;
        }

        $this->ensureDirectoryExists($this->newParser->viewPath());

        return File::copy("{$this->parser->viewPath()}", $this->newParser->viewPath());
    }
}
