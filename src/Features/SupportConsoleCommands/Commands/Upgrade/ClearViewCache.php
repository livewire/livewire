<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Illuminate\Support\Facades\File;
use Livewire\Features\SupportConsoleCommands\Commands\ComponentParser;
use Livewire\Features\SupportConsoleCommands\Commands\ComponentParserFromExistingComponent;
use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class ClearViewCache extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        $console->call('view:clear');

        return $next($console);
    }
}
