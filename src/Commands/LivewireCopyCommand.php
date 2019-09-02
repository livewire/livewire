<?php

namespace Livewire\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Livewire\LivewireComponentsFinder;
use Illuminate\Console\DetectsApplicationNamespace;

class LivewireCopyCommand extends Command
{
    use DetectsApplicationNamespace;

    protected $signature = 'livewire:cp {name} {newname}';

    protected $description = 'Copy a Livewire component and it\'s corresponding blade view.';

    protected $parser;

    public function handle()
    {
        $this->parser = new LivewireRenameCommandParser(
            app_path(),
            head(config('view.paths')),
            $this->argument('name'),
            $this->argument('newname')
        );

        $class = $this->copyClass();
        $view = $this->copyView();

        $this->refreshComponentAutodiscovery();

        ($class && $view) && $this->line("<options=bold,reverse;fg=green> COMPONENT COPIED </> 🤙\n");
        $class && $this->line("<options=bold;fg=green>CLASS:</> {$this->parser->classPath()} <options=bold;fg=green>=></> {$this->parser->relativeNewClassPath()}");
        $view && $this->line("<options=bold;fg=green>VIEW:</>  {$this->parser->viewPath()} <options=bold;fg=green>=></> {$this->parser->relativeNewViewPath()}");
    }

    protected function copyClass()
    {
        if (File::exists($this->parser->newClassPath())) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Class already exists:</> {$this->parser->relativeNewClassPath()}");

            return false;
        }

        $this->ensureDirectoryExists($this->parser->newClassPath());

        return File::put($this->parser->newClassPath(), $this->parser->classContents());
    }

    protected function copyView()
    {
        if (File::exists($this->parser->newViewPath())) {
            $this->line("<fg=red;options=bold>View already exists:</> {$this->parser->relativeNewViewPath()}");

            return false;
        }

        $this->ensureDirectoryExists($this->parser->newViewPath());

        return File::copy("{$this->parser->viewPath()}", $this->parser->newViewPath());
    }

    protected function ensureDirectoryExists($path)
    {
        if (! File::isDirectory(dirname($path))) {
            File::makeDirectory(dirname($path), 0777, $recursive = true, $force = true);
        }
    }

    public function refreshComponentAutodiscovery()
    {
        app(LivewireComponentsFinder::class)->build();
    }
}
