<?php

namespace Livewire\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Livewire\LivewireComponentsFinder;
use Illuminate\Console\DetectsApplicationNamespace;

class LivewireDestroyCommand extends Command
{
    use DetectsApplicationNamespace;

    protected $signature = 'livewire:destroy {name} {--force}';

    protected $description = 'Remove a Livewire component\'s class and view.';

    protected $parser;

    public function handle()
    {
        $this->parser = new LivewireMakeCommandParser(
            config('livewire.class_namespace', 'App\\Http\\Livewire'),
            config('livewire.view_path', resource_path('views/livewire')),
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
        $class && $this->line("<options=bold;fg=yellow>CLASS:</> {$this->parser->relativeClassPath()}");
        $view && $this->line("<options=bold;fg=yellow>VIEW:</>  {$this->parser->relativeViewPath()}");
    }

    protected function removeClass($force = false)
    {
        $classPath = $this->parser->classPath();

        if (! File::exists($classPath) && ! $force) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Class doesn't exist:</> {$this->parser->relativeClassPath()}");

            return false;
        }

        File::delete($classPath);

        return $classPath;
    }

    protected function removeView($force = false)
    {
        $viewPath = $this->parser->viewPath();

        if (! File::exists($viewPath) && ! $force) {
            $this->line("<fg=red;options=bold>View doesn't exist:</> {$this->parser->relativeViewPath()}");

            return false;
        }

        File::delete($viewPath);

        return $viewPath;
    }

    public function refreshComponentAutodiscovery()
    {
        app(LivewireComponentsFinder::class)->build();
    }
}
