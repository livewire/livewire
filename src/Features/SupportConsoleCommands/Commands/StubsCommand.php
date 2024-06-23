<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'livewire:stubs')]
class StubsCommand extends Command
{
    protected $signature = 'livewire:stubs';

    protected $description = 'Publish Livewire stubs';

    protected $parser;

    public function handle()
    {
        if (! is_dir($stubsPath = base_path('stubs'))) {
            (new Filesystem)->makeDirectory($stubsPath);
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

        file_put_contents(
            $stubsPath.'/livewire.pest.stub',
            file_get_contents(__DIR__.'/livewire.pest.stub')
        );

        file_put_contents(
            $stubsPath.'/livewire.form.stub',
            file_get_contents(__DIR__.'/livewire.form.stub')
        );

        file_put_contents(
            $stubsPath.'/livewire.attribute.stub',
            file_get_contents(__DIR__.'/livewire.attribute.stub')
        );

        $this->info('Stubs published successfully.');
    }
}
