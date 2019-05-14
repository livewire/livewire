<?php

namespace Livewire\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\DetectsApplicationNamespace;
use Illuminate\Support\Facades\File;
use Livewire\LivewireComponentsFinder;

class LivewireMakeCommand extends Command
{
    use DetectsApplicationNamespace;

    protected $signature = 'make:livewire {name} {--force}';

    protected $description = 'Create a new Livewire component and it\'s corresponding blade view.';

    protected $parser;

    public function handle()
    {
        $this->parser = new LivewireMakeCommandParser(
            app_path(),
            head(config('view.paths')),
            $this->argument('name')
        );


        $force = $this->option('force');

        $class = $this->createClass($force);
        $view = $this->createView($force);

        $this->refreshComponentAutodiscovery();

        ($class && $view) && $this->info("👍  Files created:");
        $class && $this->info("-> [{$class}]");
        $view && $this->info("-> [{$view}]");
    }

    protected function createClass($force = false)
    {
        $classPath = $this->parser->classPath();

        if (File::exists($classPath) && ! $force) {
            $this->error("Component class already exists [{$classPath}]");
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
            $this->error("Component view already exists [{$viewPath}]");
            return false;
        }

        $this->ensureDirectoryExists($viewPath);

        File::put($viewPath, $this->parser->viewContents());

        return $viewPath;
    }

    protected function ensureDirectoryExists($path)
    {
        if ( ! File::isDirectory(dirname($path))) {
            File::makeDirectory(dirname($path), 0777, $recursive = true, $force = true);
        }
    }

    public function refreshComponentAutodiscovery()
    {
        app(LivewireComponentsFinder::class)->build();
    }
}
