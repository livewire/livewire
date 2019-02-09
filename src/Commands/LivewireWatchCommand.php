<?php

namespace Livewire\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Yosymfony\ResourceWatcher\Crc32ContentHash;
use Yosymfony\ResourceWatcher\ResourceCacheMemory;
use Yosymfony\ResourceWatcher\ResourceWatcher;

class LivewireWatchCommand extends Command
{
    protected $signature = 'livewire:watch';

    protected $description = '@todo';

    public function handle()
    {
        $watcher = $this->fileWatcher();

        // This is for a goto() later on.
        start_process:

        $process = $this->runLivewireStartCommand();

        // @todo - this loop is resource costly - maybe too fast?
        while ($process->isRunning()) {
            if ($watcher->findChanges()->hasChanges()) {
                $process->stop();

                $this->info('[Livewire Restarted]');

                goto start_process;
            }

            usleep($pointTwoFiveSeconds = 250000);
        }
    }

    protected function fileWatcher()
    {
        return new ResourceWatcher(
            new ResourceCacheMemory(),
            $this->filesToWatch(),
            new Crc32ContentHash()
        );
    }

    protected function filesToWatch()
    {
        return (new Finder())
            ->files()
            ->name('*.php')
            ->in([
                app_path(),
                base_path('tests'),
                resource_path('views'),
            ]);
    }

    public function runLivewireStartCommand()
    {
        // "Come in start command...start command, do you copy?"
        $process = new Process('php artisan livewire:start');

        $process->start(function ($type, $output) use ($process) {
            // Capture and forward child process ("start" command) output.
            if ($type === $process::OUT) {
                fwrite(STDOUT, $output);
            } else {
                $this->alert($output);
            }
        });
    }
}
