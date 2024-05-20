<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Illuminate\Console\Command;

class ReplaceTemporaryUploadedFileNamespace extends UpgradeStep
{
    public function handle(Command $console, \Closure $next)
    {
        $console->newLine(2);
        $console->line("<fg=#FB70A9;bg=black;options=bold,reverse> Livewire Temporary uploaded file </>");
        $console->newLine();
        $console->line('In v2 you might have used the Livewire\TemporaryUploadedFile class.');
        $console->line('For version 3, Livewire has the file on this new namespace Livewire\Features\SupportFileUploads\TemporaryUploadedFile');
        $console->line('This step is fully automated.');
        $console->confirm('Ready to continue?');

        $this->interactiveReplacement(
            console: $console,
            title: 'Livewire\TemporaryUploadedFile is now Livewire\Features\SupportFileUploads\TemporaryUploadedFile.',
            before: 'Livewire\TemporaryUploadedFile',
            after: 'Livewire\Features\SupportFileUploads\TemporaryUploadedFile',
            pattern: '/Livewire\\TemporaryUploadedFile/',
            replacement: 'Livewire\\Features\\SupportFileUploads\\TemporaryUploadedFile',
        );
        if($console->confirm('Continue?', true))
        {
            return $next($console);
        }
    }
}
