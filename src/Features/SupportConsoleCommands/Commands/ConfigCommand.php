<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'livewire:config')]
class ConfigCommand extends Command
{
    protected $signature = 'livewire:config';

    protected $description = 'Publish Livewire config file';

    public function handle()
    {
        $this->call('vendor:publish', ['--tag' => 'livewire:config', '--force' => true]);
    }
}
