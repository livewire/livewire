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
        $finder = (new Finder())
            ->files()
            ->name('*.php')
            ->in([
                app_path(),
                base_path('tests'),
                resource_path('views'),
            ]);

        $watcher = new ResourceWatcher(
            new ResourceCacheMemory(),
            $finder,
            new Crc32ContentHash()
        );

        start_process:

        $process = new Process('php artisan livewire:start');
        $process->start(function ($type, $output) use ($process) {
            if ($type === $process::OUT) {
                fwrite(STDOUT, $output);
            } else {
                $this->alert($output);
            }
        });

        // @todo - this loop is resource costly - maybe too fast?
        while ($process->isRunning()) {
            if ($watcher->findChanges()->hasChanges()) {
                $process->stop();

                $this->info('[Livewire process restarted]');

                goto start_process;
            }

            usleep($pointFiveSeconds = 250000);
        }
    }
}
