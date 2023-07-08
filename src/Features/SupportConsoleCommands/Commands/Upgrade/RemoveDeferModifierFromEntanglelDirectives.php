<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class RemoveDeferModifierFromEntanglelDirectives extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        $console->line("<fg=#FB70A9;bg=black;options=bold,reverse> The @entangle(...) directive is now deferred by default. </>");
        $console->newLine();
        $console->line('This means all <options=underscore>@entangle(...).defer</> directives must be changed to <options=underscore>@entangle(...)</>.');

        $confirm = $console->confirm('Would you like to change all occurrences of @entangle(...).defer to @entangle(...)?', true);

        if (! $confirm) {
            return $next($console);
        }

        $console->line('Changing all occurrences of @entangle(...).defer to @entangle(...)...');
        $console->newLine();

        $console->table(['File', 'Occurrences'], $this->patternReplacement('/@entangle\((.*)\)\.defer/', '@entangle($1)'));

        return $next($console);
    }
}
