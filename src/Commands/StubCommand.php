<?php

namespace Livewire\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;

class StubCommand extends Command
{
    protected $signature = 'livewire:stub {name}';

    protected $description = 'Create Livewire stubs.';

    public function handle()
    {
        $this->info('you ran the command');
    }

    protected function ensureDirectoryExists($path)
    {
        if (! File::isDirectory(dirname($path))) {
            File::makeDirectory(dirname($path), 0777, $recursive = true, $force = true);
        }
    }
}
