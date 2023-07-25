<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class UpdateForgetComputedCalls extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        $this->interactiveReplacement(
            console: $console,
            title: 'The forgetComputed component method is replaced by php\'s unset function',
            before: '$this->forgetComputed()',
            after: 'unset()',
            pattern: '/\$this->forgetComputed\(((?:[^)(]|\((?:[^)(]|\((?:[^)(]|\([^)(]*\))*\))*\))*)\)/',
            replacement: 'unset($this->$1)',
            directories: ['app'],
            subjectName: 'calls'
        );

        return $next($console);
    }
}
