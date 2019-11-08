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
        $this->ensureDirectoryExists(app_path('Http/Livewire/Stubs'));
        $this->ensureDirectoryExists('resources/views/livewire/stubs');
        $classCreated = $this->createViewStubIfDoesNotExist($component);
        $viewCreated = $this->createClassStubIfDoesNotExist($componentClass);
        if ($classCreated) {
            $this->info('Class '.$componentClass.'.stub created');
        }
        if ($viewCreated) {
            $this->info('View '.$component.'.stub created');
        }
    }

    protected function ensureDirectoryExists($path)
    {
        if ( !File::isDirectory($path)) {
            File::makeDirectory($path, 0777, $recursive = true, $force = true);
        }
    }

    protected function createClassStubIfDoesNotExist($name)
    {
        if ( !File::exists('app/Http/Livewire/Stubs/'.$name.'.stub')) {
            File::put('app/Http/Livewire/Stubs/'.$name.'.stub',
                file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'Component.stub'));

            return true;
        } else {
            $this->error('Class stub already exists');

            return false;
        }
    }

    protected function createViewStubIfDoesNotExist($name)
    {
        if ( !File::exists('resources/views/livewire/stubs/'.$name.'.stub')) {
            File::put('resources/views/livewire/stubs/'.$name.'.stub',
                file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'view.stub'));

            return true;
        } else {
            $this->error('View stub already exists');

            return false;
        }
    }
}
