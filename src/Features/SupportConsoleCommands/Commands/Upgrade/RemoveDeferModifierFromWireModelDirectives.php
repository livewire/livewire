<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class RemoveDeferModifierFromWireModelDirectives extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        $console->line("<fg=#FB70A9;bg=black;options=bold,reverse> The wire:model directive is now deferred by default. </>");
        $console->newLine();
        $console->line('This means all <options=underscore>wire:model.defer</> directives must be changed to <options=underscore>wire:model</>.');

        $confirm = $console->confirm('Would you like to change all occurrences of wire:model.defer to wire:model?', true);

        if (! $confirm) {
            return $next($console);
        }

        $console->line('Changing all occurrences of wire:model.defer to wire:model...');
        $console->newLine();

        $console->table(['File', 'Occurrences'], $this->patternReplacement('/wire:model.defer/', 'wire:model'));

        return $next($console);
    }
}
