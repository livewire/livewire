<?php

namespace Livewire\Commands;

use Illuminate\Support\Facades\File;

class LivewireDeleteCommand extends LivewireFileManipulationCommand
{
    protected $signature = 'livewire:delete {name} {--force}';

    protected $description = 'Delete a Livewire component\'s class and view. (Alias livewire:rm)';

    public function handle()
    {
        $this->parser = new LivewireFileManipulationCommandParser(
            app_path(),
            head(config('view.paths')),
            $this->argument('name')
        );

        if (! $force = $this->option('force')) {
            $shouldContinue = $this->confirm(
                "<fg=yellow>Are you sure you want to delete the following files?</>\n\n{$this->parser->relativeClassPath()}\n{$this->parser->relativeViewPath()}\n"
            );

            if (! $shouldContinue) {
                return;
            }
        }

        $class = $this->removeClass($force);
        $view = $this->removeView($force);

        $this->refreshComponentAutodiscovery();

        ($class && $view) && $this->line("<options=bold,reverse;fg=yellow> COMPONENT DESTROYED </> 🦖💫\n");
        $class && $this->line("<options=bold;fg=yellow>CLASS:</> {$this->parser->component->relativeClassPath()}");
        $view && $this->line("<options=bold;fg=yellow>VIEW:</>  {$this->parser->component->relativeViewPath()}");
    }

    protected function removeClass($force = false)
    {
        $classPath = $this->parser->component->classPath();

        if (! File::exists($classPath) && ! $force) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Class doesn't exist:</> {$this->parser->component->relativeClassPath()}");

            return false;
        }

        File::delete($classPath);

        return $classPath;
    }

    protected function removeView($force = false)
    {
        $viewPath = $this->parser->component->viewPath();

        if (! File::exists($viewPath) && ! $force) {
            $this->line("<fg=red;options=bold>View doesn't exist:</> {$this->parser->component->relativeViewPath()}");

            return false;
        }

        File::delete($viewPath);

        return $viewPath;
    }
}
