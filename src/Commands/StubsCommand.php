<?php

namespace Livewire\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class StubsCommand extends Command
{
    protected $signature = 'livewire:stubs {--subDirectory=}';

    protected $description = 'Publish Livewire stubs';

    protected $parser;

    public function handle()
    {
        $subDirectory='';
        if(!empty($this->option('subDirectory'))){
            $subDirectory = '/'.$this->option('subDirectory');
        }
        if (! is_dir($stubsPath = base_path('stubs'.$subDirectory))) {
            (new Filesystem)->makeDirectory($stubsPath,0755,true);
        }

        file_put_contents(
            $stubsPath.'/livewire.stub',
            file_get_contents(__DIR__.'/livewire.stub')
        );

        file_put_contents(
            $stubsPath.'/livewire.inline.stub',
            file_get_contents(__DIR__.'/livewire.inline.stub')
        );

        file_put_contents(
            $stubsPath.'/livewire.view.stub',
            file_get_contents(__DIR__.'/livewire.view.stub')
        );

        file_put_contents(
            $stubsPath.'/livewire.test.stub',
            file_get_contents(__DIR__.'/livewire.test.stub')
        );

        $this->info('Stubs published successfully.');
    }
}
