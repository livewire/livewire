<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class RemovePrefetchModifierFromWireClickDirective extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        $this->interactiveReplacement(
            console: $console,
            title: 'The wire:click.prefetch modifier has been removed.',
            before: 'wire:click.prefetch',
            after: 'wire:click',
            pattern: '/wire:click\.prefetch/',
            replacement: 'wire:click',
        );

        return $next($console);
    }
}
