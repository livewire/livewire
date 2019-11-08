<?php

namespace Livewire\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class StubCommand extends Command
{
    protected $signature = 'livewire:stub {name}';

    protected $description = 'Create Livewire stubs.';

    public function handle()
    {
        $input = preg_split('/[.]+/', $this->argument('name'));
        $component = Str::kebab(array_pop($input));
        $componentClass = Str::studly($component);
        $this->ensureDirectoryExists('app/Http/Livewire/Stubs');
        $this->ensureDirectoryExists('resources/views/livewire/stubs');
        $this->createViewStub($component);
        $this->createClassStub($componentClass);
        $this->info('you ran the command with name: ' . $this->argument('name'));
    }

    protected function ensureDirectoryExists($path)
    {
        if (! File::isDirectory($path)) {
            File::makeDirectory($path, 0777, $recursive = true, $force = true);
        }
    }

    protected function createClassStub($name)
    {
        File::put('app/Http/Livewire/Stubs/'.$name.'.stub', file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'Component.stub'));
    }

    protected function createViewStub($name)
    {
        File::put('resources/views/livewire/stubs/'.$name.'.stub', file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'view.stub'));
    }
}
