<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class ChangeTestAssertionMethods extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        $this->interactiveReplacement(
            console: $console,
            title: 'assertEmitted is now assertDispatched.',
            before: 'assertEmitted',
            after: 'assertDispatched',
            pattern: '/assertEmitted\((.*)\)/',
            replacement: 'assertDispatched($1)',
            directories: 'tests'
        );

        $this->interactiveReplacement(
            console: $console,
            title: 'assertEmittedTo is now assertDispatchedTo.',
            before: 'assertEmittedTo',
            after: 'assertDispatchedTo',
            pattern: '/assertEmittedTo\((.*)\)/',
            replacement: 'assertDispatchedTo($1)',
            directories: 'tests'
        );

        $this->interactiveReplacement(
            console: $console,
            title: 'assertNotEmitted is now assertNotDispatched.',
            before: 'assertNotEmitted',
            after: 'assertNotDispatched',
            pattern: '/assertNotEmitted\((.*)\)/',
            replacement: 'assertNotDispatched($1)',
            directories: 'tests'
        );

        $this->interactiveReplacement(
            console: $console,
            title: 'assertEmittedUp is no longer available.',
            before: 'assertEmittedUp',
            after: '<removed>',
            pattern: '/->assertEmittedUp\(.*\)/',
            replacement: '',
            directories: 'tests'
        );

        return $next($console);
    }
}
