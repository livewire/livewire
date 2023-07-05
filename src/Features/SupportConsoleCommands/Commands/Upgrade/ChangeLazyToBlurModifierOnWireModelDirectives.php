<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class ChangeLazyToBlurModifierOnWireModelDirectives extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        $console->line("<fg=#FB70A9;bg=black;options=bold,reverse> The wire:model.lazy is now wire:model.blur. </>");
        $console->newLine();
        $console->line('This means all <options=underscore>wire:model.lazy</> directives must be changed to <options=underscore>wire:model.blur</>.');

        $confirm = $console->confirm('Would you like to change all occurences of wire:model.lazy to wire:model.blur?', true);

        if (! $confirm) {
            return $next($console);
        }

        $console->line('Changing all occurrences of wire:model.lazy to wire:model.blur...');
        $console->newLine();

        $console->table(['File', 'Occurrences'], $this->patternReplacement('/wire:model.lazy/', 'wire:model.blur'));

        return $next($console);
    }
}
