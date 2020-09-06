<?php

namespace Livewire\Commands;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    protected $signature = 'livewire:publish {--assets : Indicates if Livewire\'s front-end assets should be published}';

    protected $description = 'Publish Livewire configuration';

    public function handle()
    {
        $this->call('vendor:publish', ['--tag' => 'livewire:config', '--force' => true]);

        if ($this->option('assets')) {
            $this->call('vendor:publish', ['--tag' => 'livewire:assets', '--force' => true]);
        }

    }
}
