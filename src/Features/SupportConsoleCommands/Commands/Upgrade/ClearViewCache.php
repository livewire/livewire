<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class ClearViewCache extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        $console->call('view:clear');

        return $next($console);
    }
}
