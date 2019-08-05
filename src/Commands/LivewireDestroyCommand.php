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
            app_path(),
            head(config('view.paths')),
            $this->argument('name')
        );

        if (! $force = $this->option('force')) {
            $shouldContinue = $this->confirm(
                "Are you sure you want to delete the following files?\n\n{$this->parser->classPath()}\n{$this->parser->viewPath()}\n"
            );

            if (! $shouldContinue) {
                return;
            }
        }

        $class = $this->removeClass($force);
        $view = $this->removeView($force);

        $this->refreshComponentAutodiscovery();

        ($class && $view) && $this->info('👍  Files removed:');
        $class && $this->info("-> [{$class}]");
        $view && $this->info("-> [{$view}]");
    }

    protected function removeClass($force = false)
    {
        $classPath = $this->parser->classPath();

        if (! File::exists($classPath) && ! $force) {
            $this->error("Component class doesn't exist [{$classPath}]");

            return false;
        }

        File::delete($classPath);

        return $classPath;
    }

    protected function removeView($force = false)
    {
        $viewPath = $this->parser->viewPath();

        if (! File::exists($viewPath) && ! $force) {
            $this->error("Component view doesn't exist [{$viewPath}]");

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
