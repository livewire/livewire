<?php

namespace Livewire\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Livewire\FileUploadConfiguration;

class CleanupUploadedFilesJob implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    public $tries = 1;
    public $timeout = 600;

    public function handle()
    {
        $storage = FileUploadConfiguration::storage();

        foreach ($storage->allFiles(FileUploadConfiguration::path()) as $filePathname) {
            // On busy websites, this cleanup code can run in multiple threads causing part of the output
            // of allFiles() to have already been deleted by another thread.
            if (! $storage->exists($filePathname)) continue;

            $yesterdaysStamp = now()->subDay()->timestamp;
            if ($yesterdaysStamp > $storage->lastModified($filePathname)) {
                $storage->delete($filePathname);
            }
        }
    }

    public function uniqueId()
    {
        return self::class;
    }
}
