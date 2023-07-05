<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Pipeline;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\AddLiveModifierToWireModelDirectives;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\ChangeDefaultNamespace;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\ShowUpgradeIntroduction;

class UpgradeCommand extends Command
{
    protected $signature = 'livewire:upgrade';

    protected $description = 'Interactive upgrade helper to migrate from v2 to v3';

    public function handle()
    {
        Pipeline::send($this)->through([
            ShowUpgradeIntroduction::class,
            ChangeDefaultNamespace::class,
            AddLiveModifierToWireModelDirectives::class,
        ])->thenReturn();
    }
}
