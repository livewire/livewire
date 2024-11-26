<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Illuminate\Console\Command;

class ThirdPartyUpgradeNotice extends UpgradeStep
{
    public function handle(Command $console, \Closure $next)
    {
        $console->line('<fg=#FB70A9;bg=black;options=bold,reverse> Third-party package upgrade 🚀 </>');
        $console->newLine();
        $console->comment('!! Please be aware that the following upgrade steps are registered by third-parties !!');
        $console->newLine();
        $console->newLine();
        $console->line('You can abort this command at any time by pressing ctrl+c.');

        if($console->confirm('Ready to continue?', true))
        {
            return $next($console);
        }
    }
}
