<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class AddLiveModifierToEntangleDirectives extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        $this->interactiveReplacement(
            console: $console,
            title: 'The @entangle(...) directive is now deferred by default.',
            before: '@entangle(...)',
            after: '@entangle(...).live',
            pattern: '/@entangle\(((?:[^)(]|\((?:[^)(]|\((?:[^)(]|\([^)(]*\))*\))*\))*)\)(?!\.(?:defer|live))/',
            replacement: '@entangle($1).live',
        );

        $this->interactiveReplacement(
            console: $console,
            title: 'The $wire.entangle function is now deferred by default and has been changed to $wire.$entangle.',
            before: '$wire.entangle(...)',
            after: '$wire.$entangle(..., true)',
            pattern: '/\$wire\.entangle\(((?:[^)(]|\((?:[^)(]|\((?:[^)(]|\([^)(]*\))*\))*\))*)\)(?!\.(?:defer))/',
            replacement: '$wire.$entangle($1, true)',
            directories: ['resources/views', 'resources/js']
        );

        return $next($console);
    }
}
