<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Illuminate\Console\Command;

class ReplaceTemporaryUploadedFileNamespace extends UpgradeStep
{
    public function handle(Command $console, \Closure $next)
    {
        $this->interactiveReplacement(
            console: $console,
            title: 'Livewire\TemporaryUploadedFile is now Livewire\Features\SupportFileUploads\TemporaryUploadedFile.',
            before: 'Livewire\TemporaryUploadedFile',
            after: 'Livewire\Features\SupportFileUploads\TemporaryUploadedFile',
            pattern: '/Livewire\\\\TemporaryUploadedFile/',
            replacement: "Livewire\\Features\\SupportFileUploads\\TemporaryUploadedFile",
            directories: ['app', 'tests', 'resources/views']
        );
        if ($console->confirm('Continue?', true)) {
            return $next($console);
        }
    }
}
