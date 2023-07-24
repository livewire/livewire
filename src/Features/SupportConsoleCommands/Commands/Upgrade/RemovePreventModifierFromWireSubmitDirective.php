<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class RemovePreventModifierFromWireSubmitDirective extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        $this->interactiveReplacement(
            console: $console,
            title: 'The wire:submit directive now prevents submission by default.',
            before: 'wire:submit.prevent',
            after: 'wire:submit',
            pattern: '/wire:submit\.prevent/',
            replacement: 'wire:submit',
        );

        return $next($console);
    }
}
