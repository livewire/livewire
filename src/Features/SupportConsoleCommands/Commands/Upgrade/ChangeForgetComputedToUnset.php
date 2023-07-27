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
            pattern: '/\$this->forgetComputed\((.*?)\);/',
            replacement: function($matches) {
                $replacement = '';

                if(isset($matches[1])) {
                    preg_match_all('/(?:\'|")(.*?)(?:\'|")/', $matches[1], $keys);

                    $replacement .= 'unset(';
                    foreach($keys[1] ?? [] as $key) {
                        $replacement .= '$this->' . $key . ', ';
                    }
                    $replacement = rtrim($replacement, ', ');
                    $replacement .= ');';
                }

                // Leave unchanged if no replacement was possible.
                return $replacement ?: $matches[0];
            },
            directories: 'app',
        );

        return $next($console);
    }
}
