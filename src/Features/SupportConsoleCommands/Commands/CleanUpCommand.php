<?php

namespace Features\SupportConsoleCommands\Commands;

use Illuminate\Console\Command;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\WithFileUploads;

class CleanUpCommand extends Command
{
    use WithFileUploads;

    protected $signature = 'livewire:upload-cleanup';

    protected $description = 'Clean up all uploaded temporary files';

    public function handle(): void
    {
        if (FileUploadConfiguration::isUsingS3()) {
            $this->error('You are using S3 for file uploads. No temporary files to clean up.');

            return;
        }

        $this->cleanupOldUploads();

        $this->info('Temporary files cleaned up successfully.');
    }
}
