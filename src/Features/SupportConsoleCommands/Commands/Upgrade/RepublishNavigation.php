<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class RepublishNavigation extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        if($this->filesystem()->directoryExists('resources/views/vendor/livewire')) {
            $console->line('<fg=#FB70A9;bg=black;options=bold,reverse> The Livewire pagination views have changed. </>');
            $console->newLine();

            $console->line('Republishing of the pagination views is required.');

            $confirm = $console->confirm('Do you want to republish the pagination views?', true);

            if($confirm) {
                $console->call('vendor:publish', [
                    '--tag' => 'livewire:pagination',
                    '--force' => true,
                ]);
            }
        }

        return $next($console);
    }
}
