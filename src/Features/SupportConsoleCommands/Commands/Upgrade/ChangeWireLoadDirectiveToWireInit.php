<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class ChangeWireLoadDirectiveToWireInit extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        $this->interactiveReplacement(
            console: $console,
            title: 'The livewire:load is now livewire:init.',
            before: 'livewire:load',
            after: 'livewire:init',
            pattern: '/livewire:load/',
            replacement: 'livewire:init',
            directories: ['resources/views', 'resources/js']
        );

        return $next($console);
    }
}
