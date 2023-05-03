<?php

namespace Livewire\Commands;

use Illuminate\Console\Command;
use Livewire\FileUploadConfiguration;
use Livewire\Jobs\CleanupUploadedFilesJob;

class CleanupCommand extends Command
{
    protected $signature = 'livewire:upload-cleanup';

    protected $description = 'Dispatch job to delete files older than 24 hours';

    public function handle()
    {
        if ( FileUploadConfiguration::isUsingS3()) {
            $this->error("This command is not to be used with S3 driver.");

            return;
        }

        CleanupUploadedFilesJob::dispatch();

        $this->info('Dispatched CleanupUploadedFilesJob job');
    }
}
