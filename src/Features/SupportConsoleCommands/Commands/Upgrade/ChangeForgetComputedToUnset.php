<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class ChangeForgetComputedToUnset extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        $this->interactiveReplacement(
            console: $console,
            title: 'The forgetComputed component method is replaced by PHP\'s unset function',
            before: '$this->forgetComputed(\'title\')',
            after: 'unset($this->title)',
            pattern: '/\$this->forgetComputed\(((?:[^)(]|\((?:[^)(]|\((?:[^)(]|\([^)(]*\))*\))*\))*)\)/',
            replacement: 'unset($this->$1)',
            directories: 'app',
        );

        return $next($console);
    }
}
