<?php

namespace Livewire\Commands;

use Illuminate\Support\Facades\File;

class LivewireMakeCommand extends LivewireFileManipulationCommand
{
    protected $signature = 'make:livewire {name} {--force}';

    protected $description = 'Create a new Livewire component and it\'s corresponding blade view.';

    public function handle()
    {
        $this->parser = new LivewireFileManipulationCommandParser(
            app_path(),
            head(config('view.paths')),
            $this->argument('name')
        );

        $force = $this->option('force');

        $showWelcomeMessage = $this->isFirstTimeMakingAComponent();

        $class = $this->createClass($force);
        $view = $this->createView($force);

        $this->refreshComponentAutodiscovery();

        ($class && $view) && $this->line("<options=bold,reverse;fg=green> COMPONENT CREATED </> 🤙\n");
        $class && $this->line("<options=bold;fg=green>CLASS:</> {$this->parser->relativeClassPath()}");
        $view && $this->line("<options=bold;fg=green>VIEW:</>  {$this->parser->relativeViewPath()}");

        if ($showWelcomeMessage) {
            $this->writeWelcomeMessage();
        }
    }

    protected function createClass($force = false)
    {
        $classPath = $this->parser->classPath();

        if (File::exists($classPath) && ! $force) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Class already exists:</> {$this->parser->relativeClassPath()}");

            return false;
        }

        $this->ensureDirectoryExists($classPath);

        File::put($classPath, $this->parser->classContents());

        return $classPath;
    }

    protected function createView($force = false)
    {
        $viewPath = $this->parser->viewPath();

        if (File::exists($viewPath) && ! $force) {
            $this->line("<fg=red;options=bold>View already exists:</> {$this->parser->relativeViewPath()}");

            return false;
        }

        $this->ensureDirectoryExists($viewPath);

        File::put($viewPath, $this->parser->viewContents());

        return $viewPath;
    }
}
