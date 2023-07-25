<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class RemoveDeferModifierFromWireModelDirectives extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        $this->interactiveReplacement(
            console: $console,
            title: 'The wire:model directive is now deferred by default.',
            before: 'wire:model.defer',
            after: 'wire:model',
            pattern: '/wire:model\.defer/',
            replacement: 'wire:model',
        );

        return $next($console);
    }
}
