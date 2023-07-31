<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class ChangeComputedProperties extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        $this->interactiveReplacement(
            console: $console,
            title: 'The getComputedProperty gets replaced by property name with attribute.',
            before: 'public function getFooBarProperty()',
            after: "#[\Livewire\Attributes\Computed]\n  public function fooBar()",
            pattern: '/(.*)public function get(.+)Property\(\)/',
            replacement: fn ($matches) => isset($matches[2])
                ? "{$matches[1]}#[\Livewire\Attributes\Computed]\n{$matches[1]}public function ".str($matches[2])->lcfirst().'()'
                : $matches[0],
            directories: 'app',
        );

        return $next($console);
    }
}
