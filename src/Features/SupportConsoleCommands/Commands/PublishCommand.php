<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    protected $signature = 'livewire:publish
        { --assets : Indicates if Livewire\'s front-end assets should be published }
        { --config : Indicates if Livewire\'s config file should be published }
        { --pagination : Indicates if Livewire\'s pagination views should be published }
        { --lazy-loading : Indicates if Livewire\'s lazy-loading placeholder view should be published }
        ';

    protected $description = 'Publish Livewire configuration';

    public function handle()
    {
        if ($this->option('assets')) {
            $this->publishAssets();
        } elseif ($this->option('config')) {
            $this->publishConfig();
        } elseif ($this->option('pagination')) {
            $this->publishPagination();
        } elseif ($this->option('lazy-loading')) {
            $this->publishLazyLoadingPlaceholder();
        } else {
            $this->publishAssets();
            $this->publishConfig();
            $this->publishPagination();
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

    public function publishPagination()
    {
        $this->call('vendor:publish', ['--tag' => 'livewire:pagination', '--force' => true]);
    }

    public function publishLazyLoadingPlaceholder()
    {
        $this->call('vendor:publish', ['--tag' => 'livewire:lazy-loading', '--force' => true]);
    }
}
