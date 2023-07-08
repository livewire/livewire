<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class AddLiveModifierToWireModelDirectives extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        $this->interactiveReplacement(
            console: $console,
            title: 'The wire:model directive is now deferred by default.',
            before: 'wire:model',
            after: 'wire:model.live',
            pattern: '/wire:model(?!\.(?:defer|lazy|live))/',
            replacement: 'wire:model.live',
        );

        return $next($console);
    }
}
