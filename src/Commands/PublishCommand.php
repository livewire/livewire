<?php

namespace Livewire\Commands;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    protected $signature = 'livewire:publish 
        { --assets : Indicates if Livewire\'s front-end assets should be published }
        { --config : Indicates if Livewire\'s config file should be published }';

    protected $description = 'Publish Livewire configuration';

    public function handle()
    {
        if ($this->option('assets')) {
            $this->publishAssets();
        } elseif ($this->option('config')) {
            $this->publishConfig();
        } else {
            $this->publishAssets();
            $this->publishConfig();
        }
    }

    public function publishAssets()
    {
        $this->call('vendor:publish', ['--tag' => 'livewire:assets', '--force' => true]);
    }

    public function publishConfig()
    {
        $this->call('vendor:publish', ['--tag' => 'livewire:config', '--force' => true]);
    }
}
