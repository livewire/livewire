<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class RemoveDeferModifierFromEntangleDirectives extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        $this->interactiveReplacement(
            console: $console,
            title: 'The @entangle(...) directive is now deferred by default.',
            before: '@entangle(...).defer',
            after: '@entangle(...)',
            pattern: '/@entangle\(((?:[^)(]|\((?:[^)(]|\((?:[^)(]|\([^)(]*\))*\))*\))*)\)\.defer/',
            replacement: '@entangle($1)',
        );

        return $next($console);
    }
}
