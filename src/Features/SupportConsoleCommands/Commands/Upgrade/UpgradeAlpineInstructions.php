<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Illuminate\Console\Command;

class UpgradeAlpineInstructions extends UpgradeStep
{
    public function handle(Command $console, \Closure $next)
    {
        $console->line('<fg=#FB70A9;bg=black;options=bold,reverse> Manual Upgrade: Remove Alpine references </>');
        $console->newLine();
        $console->line('Livewire version 3 ships with AlpineJS by default.');
        $console->line('If you use Alpine in your Livewire application, you will need to remove it and any of the plugins listed below so that Livewire\'s built-in version doesn\'t conflict with it.');
        $console->line('Livewire version 3 now also ships with the following Alpine plugins:');
        $console->line('- Intersect');
        $console->line('- Collapse');
        $console->line('- Persist');
        $console->line('- Morph');
        $console->line('- Focus');
        $console->line('- Mask');
        $console->newLine();
        $console->line('If you were accessing Alpine via JS bundle you can now import Livewire\'s ESM module instead and call Livewire.start() when ready, for example:');
        $console->newLine();
        $console->line('import { Livewire, Alpine } from \'../../vendor/livewire/livewire/dist/livewire.esm\';');
        $console->line('Alpine.plugin(yourCustomPlugin);');
        $console->line('Livewire.start();');

        if($console->confirm('Continue?', true))
        {
            return $next($console);
        }
    }
}
