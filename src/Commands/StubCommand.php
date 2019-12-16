<?php

namespace Livewire\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class StubCommand extends Command
{

    protected $signature = 'livewire:stub {name=default}';

    protected $description = 'Create Livewire stubs.';

    protected $parser;

    public function handle()
    {
        $this->parser = new StubParser(
            config('livewire.class_namespace', 'App\\Http\\Livewire'),
            config('livewire.view_path', resource_path('views/livewire')),
            $this->argument('name')
        );

        if ($this->createViewStubIfDoesNotExist()) {
            $this->line("<options=bold;fg=green>CLASS STUB:</> {$this->parser->relativeClassPath()}");
        }

        if ($this->createClassStubIfDoesNotExist()) {
            $this->line("<options=bold;fg=green>VIEW STUB:</>  {$this->parser->relativeViewPath()}");
        }
    }

    protected function ensureDirectoryExists($path)
    {
        $path = substr($path, 0, strrpos( $path, '/'));

        if (! File::isDirectory($path)) {
            File::makeDirectory($path, 0777, $recursive = true, $force = true);
        }
    }

    protected function createClassStubIfDoesNotExist()
    {
        $classPath = $this->parser->classPath();

        if (File::exists($classPath)) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Class already exists:</> {$this->parser->relativeClassPath()}");

            return false;
        }

        $this->ensureDirectoryExists($classPath);

        File::put($classPath, $this->parser->classContents());

        return $classPath;
    }

    protected function createViewStubIfDoesNotExist()
    {
        $viewPath = $this->parser->viewPath();

        if (File::exists($viewPath)) {
            $this->line("<fg=red;options=bold>View already exists:</> {$this->parser->relativeViewPath()}");

            return false;
        }

        $this->ensureDirectoryExists($viewPath);

        File::put($viewPath, $this->parser->viewContents());

        return $viewPath;
    }
}
