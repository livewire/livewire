<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class ChangeLazyToBlurModifierOnWireModelDirectives extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        $this->interactiveReplacement(
            console: $console,
            title: 'The wire:model.lazy modifier is now wire:model.blur.',
            before: 'wire:model.lazy',
            after: 'wire:model.blur',
            pattern: '/wire:model.lazy/',
            replacement: 'wire:model.blur',
        );

        return $next($console);
    }
}
