<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Illuminate\Console\Command;

class UpgradeIntroduction extends UpgradeStep
{
    public function handle(Command $console, \Closure $next)
    {
        $console->line('<fg=#FB70A9;bg=black;options=bold,reverse> LIVEWIRE v2 to v3 UPGRADE 🚀 </>');
        $console->newLine();
        $console->comment('!! Running this command multiple times may result in incorrect replacements !!');
        $console->newLine();
        $console->line('This command will help you upgrade from Livewire v2 to v3.');
        $console->newLine();
        $console->line('<options=underscore>Files will be modified in-place, so make sure you have a backup of your project before continuing.</>');
        $console->newLine();
        $console->newLine();
        $console->line('You can abort this command at any time by pressing ctrl+c.');

        if($console->confirm('Ready to continue?', true))
        {
            return $next($console);
        }
    }
}
